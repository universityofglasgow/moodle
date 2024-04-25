<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class for converting files between different file formats using Microsoft OneDrive drive.
 *
 * @package    fileconverter_onedrive
 * @copyright  2018 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace fileconverter_onedrive;

use stored_file;
use moodle_exception;
use moodle_url;
use \core_files\conversion;

/**
 * Class for converting files between different formats using unoconv.
 *
 * @package    fileconverter_onedrive
 * @copyright  2018 University of Nottingham
 * @author     Neill Magill <neill.magill@nottingham.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class converter implements \core_files\converter_interface {
    /** @var array $supported Map of output formats to input formats. */
    private static $supported = array(
        'pdf' => ['csv', 'doc', 'docx', 'odp', 'ods', 'odt', 'pot', 'potm', 'potx', 'pps', 'ppsx', 'ppsxm', 'ppt', 'pptm', 'pptx',
            'rtf', 'xls', 'xlsx'],
    );

    // Set fragment size to a multiple of 320KiB, ensuring maximum bytes in any request is less than 60MiB.
    // (see https://docs.microsoft.com/en-us/onedrive/developer/rest-api/api/driveitem_createuploadsession?view=odsp-graph-online)
    // Chose to use a multiplier of 128, so each chunked request will be 40MiB.
    /** @var int $fragementsize amount of data to be sent in each http request chunk. */
    private static $fragementsize = 320 * 1024 * 128;

    /**
     * Convert a document to a new format and return a conversion object relating to the conversion in progress.
     *
     * @param \core_files\conversion $conversion The file to be converted
     * @return \fileconverter_onedrive\converter
     */
    public function start_document_conversion(\core_files\conversion $conversion) {
        $file = $conversion->get_sourcefile();
        $format = $conversion->get('targetformat');

        try {
            // Instantiate OAuth client and REST service.
            $client = \core\oauth2\api::get_system_oauth_client($this->get_configured_oauth_issuer());
            $service = new \fileconverter_onedrive\rest($client);

            // Create upload session.
            $response = $this->create_upload_session($service, $file);

            // Actually upload the file to be converted.
            $upload = $this->upload_file_to_be_converted($file, $client, $response->uploadUrl);

            // Trigger remote conversion.
            // Microsoft OneDrive returns the location of the converted file in the Location header.
            $responseheaders = $this->request_conversion_of_uploaded_file($service, $upload->id, $format);

            // Download the converted file.
            $downloadlocation = make_request_directory() . '/' . $upload->id . '.' . $format;

            $this->download_converted_file(
                $client,
                $this->extract_named_header($responseheaders, 'Location'),
                $downloadlocation
            );

            $conversion->store_destfile_from_path($downloadlocation);
            $conversion->set('status', conversion::STATUS_COMPLETE);
            $conversion->update();

            // Clean up by deleting the file created in the remote service.
            $this->delete_lingering_remote_file($service, $upload->id);
        } catch (\Exception $e) {
            $conversion->set('status', conversion::STATUS_FAILED);
            $conversion->set('statusmessage', $e->getMessage());
        } finally {
            return $this;
        }
    }

    /**
     * Generate and serve the test document.
     *
     * @return stored_file
     */
    public function serve_test_document() {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $filerecord = [
            'contextid' => \context_system::instance()->id,
            'component' => 'test',
            'filearea' => 'fileconverter_onedrive',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'conversion_test.docx'
        ];

        // Get the fixture doc file content and generate and stored_file object.
        $fs = get_file_storage();
        $testdocx = $fs->get_file($filerecord['contextid'], $filerecord['component'], $filerecord['filearea'],
                $filerecord['itemid'], $filerecord['filepath'], $filerecord['filename']);

        if (!$testdocx) {
            $fixturefile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR .
                'fixtures' . DIRECTORY_SEPARATOR . 'source.docx';
            $testdocx = $fs->create_file_from_pathname($filerecord, $fixturefile);
        }

        $conversion = new \core_files\conversion(0, (object) [
            'targetformat' => 'pdf',
        ]);

        $conversion->set_sourcefile($testdocx);
        $conversion->create();

        // Convert the doc file to pdf and send it direct to the browser.
        $this->start_document_conversion($conversion);

        if ($conversion->get('status') === conversion::STATUS_FAILED) {
            $errors = $conversion->get('statusmessage');
            $debugging = var_export($conversion->get_errors(), true);
            throw new moodle_exception('conversionfailed', 'fileconverter_onedrive', '', $errors, $debugging);
        }

        $testfile = $conversion->get_destfile();
        readfile_accel($testfile, 'application/pdf', true);
    }

    /**
     * Poll an existing conversion for status update.
     *
     * @param conversion $conversion The file to be converted
     * @return \fileconverter_onedrive\converter;
     */
    public function poll_conversion_status(conversion $conversion) {
        return $this;
    }

    /**
     * Request the removal/deletion of a file in oneDrive.
     *
     * @param \fileconverter_onedrive\rest $restservice Microsoft OneDrive Rest API client
     * @param string $remoteidofuploadedfile OneDrive ID of the file to be removed/deleted
     *
     * @throws \Exception
     */
    private function delete_lingering_remote_file($restservice, $remoteidofuploadedfile) {
        // Cleanup.
        $deleteparams = [
            'itemid' => $remoteidofuploadedfile
        ];

        try {
            $restservice->call('delete', $deleteparams);
        } catch (\Exception $e) {
            throw new \Exception(get_string(
                'remotedeletefailed',
                'fileconverter_onedrive',
                $e->getMessage()
            ));
        }
    }

    /**
     * Extract a specifically named HTTP header from an array of HTTP headers.
     *
     * @param array $headers Array of HTTP headers
     * @param string $soughtheader Name of the sought after header
     * @return string
     */
    private function extract_named_header($headers, $soughtheader) {
        foreach ($headers as $header) {
            if (strpos($header, $soughtheader) === 0) {
                return trim(substr($header, strpos($header, ':') + 1));
            }
        }
        return '';
    }

    /**
     * Download a remote file to a local destination
     *
     * @param \core\oauth2\client $oauthclient OAuth2 client
     * @param string $downloadfrom Remote file location
     * @param string $downloadto Location to store downloaded file locally
     *
     * @throws \Exception
     */
    private function download_converted_file($oauthclient, $downloadfrom, $downloadto) {
        if (empty($downloadfrom)) {
            throw new \Exception(get_string('nodownloadurl', 'fileconverter_onedrive'));
        }

        $sourceurl = new moodle_url($downloadfrom);
        $source = $sourceurl->out(false);

        $options = ['filepath' => $downloadto, 'timeout' => 15, 'followlocation' => true, 'maxredirs' => 5];
        if (!$oauthclient->download_one($source, null, $options)) {
            throw new \Exception(get_string('downloadfailed', 'fileconverter_onedrive'));
        }
    }

    /**
     * Trigger conversion of a file in OneDrive
     *
     * @param \fileconverter_onedrive\rest $restservice Microsoft OneDrive Rest API client
     * @param string $remoteidofuploadedfile ID of Remote file in OneDrive
     * @param string $requiredformat File format to convert to (e.g. pdf)
     *
     * @return string|\stdClass|array
     * @throws \Exception
     */
    private function request_conversion_of_uploaded_file($restservice, $remoteidofuploadedfile, $requiredformat) {
        // Convert the file.
        $convertparams = [
            'itemid' => $remoteidofuploadedfile,
            'format' => $requiredformat,
        ];

        try {
            return $restservice->call('convert', $convertparams);
        } catch (\Exception $e) {
            throw new \Exception(get_string(
                'conversionrequestfailed',
                'fileconverter_onedrive',
                $e->getMessage()
            ));
        }
    }


    /**
     * Upload the file to be converted to OneDrive
     *
     * @param stored_file $filetobeconverted File to be converted
     * @param \core\oauth2\client $oauthclient OAuth2 client
     * @param string $uploadurl OneDrive upload URL
     *
     * @return Object
     * @throws \Exception
     */
    private function upload_file_to_be_converted($filetobeconverted, $oauthclient, $uploadurl) {
        $filesize = $filetobeconverted->get_filesize();
        $nofragments = ceil($filesize / self::$fragementsize);

        foreach ([new \curl(['debug' => false]), $oauthclient] as $curlinstance) {
            for ($i = 0; $i < $nofragments; $i++) {

                $chunksize = (($i + 1) == $nofragments) ? ($filesize % self::$fragementsize) : self::$fragementsize;
                $rangestart = $i * self::$fragementsize;
                $rangeend = $rangestart + $chunksize - 1;

                // Reset, then set headers.
                $curlinstance->resetHeader();

                $headers['Content-Range'] = 'bytes ' . $rangestart . '-' . $rangeend . '/' . $filesize;
                $headers['Content-Length'] = $chunksize;

                foreach ($headers as $headername => $headerval) {
                    $curlinstance->setHeader($headername . ': ' . $headerval);
                }

                // Upload fragment/chunk.
                $datachunk = stream_get_contents($filetobeconverted->get_content_file_handle(), $chunksize, $rangestart);
                $upload = $curlinstance->put($uploadurl, $datachunk, []);
            }

            if ($curlinstance->errno == 0) {
                $upload = json_decode($upload);
            }

            if (!empty($upload->id)) {
                // We can stop now - there is a valid file returned.
                break;
            }
        }

        if (empty($upload->id)) {
            throw new \Exception(get_string('missinguploadid', 'fileconverter_onedrive'));
        }

        return $upload;
    }

    /**
     * Request creation of an upload session in OneDrive
     *
     * @param \fileconverter_onedrive\rest $restservice Microsoft OneDrive Rest API client
     * @param stored_file $filetobeconverted File to be converted
     *
     * @return \stdClass
     * @throws \Exception
     */
    private function create_upload_session($restservice, $filetobeconverted) {
        global $SITE;

        $originalname = $filetobeconverted->get_filename();
        $contenthash = $filetobeconverted->get_contenthash();

        if (strpos($originalname, '.') === false) {
            throw new \Exception(get_string('missingfileextension', 'fileconverter_onedrive'));
        }

        $importextension = substr($originalname, strrpos($originalname, '.') + 1);

        // First upload the file.
        // We use a path that should be unique to the Moodle site, and not clash with the onedrive repository plugin.
        $path = '_fileconverter_onedrive_' . $SITE->shortname;
        $params = [
            'filename' => urlencode("$path/$contenthash.$importextension"),
        ];
        $behaviour = ['item' => ["@microsoft.graph.conflictBehavior" => "rename"]];

        $response = $restservice->call('create_upload', $params, json_encode($behaviour));

        if (empty($response->uploadUrl)) {
            throw new \Exception(get_string('uploadprepfailed', 'fileconverter_onedrive'));
        }

        return $response;
    }

    /**
     * Return OAuth issuer for this plugin in Moodle admin interface
     *
     * @return \core\oauth2\issuer
     * @throws \Exception
     */
    private function get_configured_oauth_issuer() {

        $issuerid = get_config('fileconverter_onedrive', 'issuerid');

        if (empty($issuerid)) {
            throw new \Exception(get_string('test_issuernotset', 'fileconverter_onedrive'));
        }

        $issuer = \core\oauth2\api::get_issuer($issuerid);

        if (empty($issuer)) {
            throw new \Exception(get_string('test_issuerinvalid', 'fileconverter_onedrive'));
        }

        return $issuer;
    }

    /**
     * Whether the plugin is configured and requirements are met.
     *
     * @return bool
     */
    public static function are_requirements_met() {
        $issuerid = get_config('fileconverter_onedrive', 'issuerid');
        if (empty($issuerid)) {
            return false;
        }

        $issuer = \core\oauth2\api::get_issuer($issuerid);
        if (empty($issuer)) {
            return false;
        }

        if (!$issuer->get('enabled')) {
            return false;
        }

        if (!$issuer->is_system_account_connected()) {
            return false;
        }

        return true;
    }

    /**
     * Whether a file conversion can be completed using this converter.
     *
     * @param string $from The source type
     * @param string $to The destination type
     * @return bool
     */
    public static function supports($from, $to) {
        // Make sure the case will match the supported array.
        $to = trim(\core_text::strtolower($to));
        $from = trim(\core_text::strtolower($from));

        if (!isset(self::$supported[$to])) {
            // The output is not supported.
            return false;
        }
        if (array_search($from, self::$supported[$to]) === false) {
            // The input is not supported by the output.
            return false;
        }
        return true;
    }

    /**
     * A list of the supported conversions.
     *
     * @return string
     */
    public function get_supported_conversions() {
        $supports = '';
        foreach (self::$supported as $output => $inputs) {
            $supports .= implode(', ', $inputs);
            $supports .= " => $output;\n\n";
        }
        return $supports;
    }
}
