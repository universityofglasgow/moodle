<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings for the quiz_essaydownload plugin
 *
 * @package   quiz_essaydownload
 * @copyright 2024 Philipp E. Imhof
 * @author    Philipp E. Imhof
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allinone'] = 'All answers in one file per attempt';
$string['allinone_help'] = 'If this option is enabled, all the answers will be grouped in one file per attempt.';
$string['attachments'] = 'Attachments';
$string['byattempt'] = 'Attempt';
$string['byquestion'] = 'Question';
$string['errorfilename'] = 'error-{$a}.txt';
$string['errorfontsize'] = 'Font size should be an integer between 6 and 50.';
$string['errormargin'] = 'All page margins must be integers between 0 and 80.';
$string['errormessage'] = 'An internal error occurred. The archive is probably incomplete. Please contact the developers of the Essay responses downloader plugin (quiz_essaydownload) and send them the details below:';
$string['essaydownload'] = 'Download essay responses';
$string['fileformat'] = 'File format';
$string['fileformat_help'] = 'You can choose between two formats:<ul><li>Portable Document Format (PDF) allows you to directly obtain a formatted document for every answer, ready for on-screen correction or printing.</li><li>Plain-text (TXT) export is faster and results in a smaller archive, which might be important for large-scale quizzes. Those files can be read with any text editor, or opened with word processor for further formatting. You might also want to choose this format, if you have some custom script to automatically convert or treat student answers in a certain way.</li></ul>';
$string['fileformatpdf'] = 'Portable Document Format (PDF)';
$string['fileformattxt'] = 'Plain-text (TXT)';
$string['firstlast'] = 'First name - Last name';
$string['fixremfontsize'] = 'Avoid chunks of unreadably small text.';
$string['fixremfontsize_help'] = 'Sometimes, Moodle\'s HTML editor <i>Atto</i> might add unwanted font size commands that will make the text unreadably small in the PDF. This setting will work around that bug.';
$string['font'] = 'Font';
$string['font_help'] = 'Note that when using the original HTML formatted text, the actual font may still be different, according to the formatting.<br><br>When using the plain-text summary, you might want to use a monospaced font.';
$string['fontmono'] = 'Monospaced';
$string['fontsans'] = 'Sans-serif';
$string['fontserif'] = 'Serif';
$string['fontsize'] = 'Font size (points)';
$string['fontsize_help'] = 'Note that when using the original HTML formatted text, the actual font size may still be different, according to the formatting';
$string['footer'] = 'Footer';
$string['forceqtsummary'] = 'Force use of simplified question text';
$string['forceqtsummary_help'] = 'In some cases, exporting the question text in HTML format can fail, e. g. if it includes images with restricted access. Checking this option will use the simplified summary of the question text, even if HTML is selected as the text source.';
$string['generaloptions'] = 'General options';
$string['groupby'] = 'Group by';
$string['groupby_help'] = 'The archive can be structured by question or by attempt:<ul><li>If you group by question, the archive will have a folder for every question. Inside each folder, you will have a folder for every attempt.</li><li>If you group by attempt, the archive will have a folder for every attempt. Inside each folder, you will have a folder for every question.</li></ul>';
$string['includeattachments'] = 'Also download possible attachments included in a student\'s answer.';
$string['includeattachments_help'] = 'Any attachment is provided as-is. Please note that attachments might contain malware.';
$string['includefooter'] = 'Add footer with page number to each page.';
$string['includequestiontext'] = 'Also include question text.';
$string['includequestiontext_help'] = 'Including the question text might be useful if your quiz uses random questions.';
$string['includestats'] = 'Include word and character count after response.';
$string['includestats_help'] = 'Note that character count will exclude whitespace.';
$string['lastfirst'] = 'Last name - First name';
$string['limitattempts'] = 'Limit attempts';
$string['linedouble'] = 'Double';
$string['lineoneandhalf'] = '1.5 lines';
$string['linesingle'] = 'Single';
$string['linespacing'] = 'Line spacing';
$string['margins'] = 'Page margins (mm): left, right, top, bottom';
$string['nameordering'] = 'Name format';
$string['noessayquestion'] = 'This quiz does not contain any essay questions.';
$string['nothingtodownload'] = 'Nothing to download';
$string['onlyone'] = 'Export at most one attempt per user according to grading method: {$a}';
$string['onlyone_help'] = 'When a quiz permits multiple attempts, the export will normally include all finished attempts of all users. However, sometimes only the last attempt (or the first one, or the one with the highest overall grade) may be relevant. With this option checked, the archive will only include (at most) one attempt per user.';
$string['page'] = 'Page format';
$string['pagea4'] = 'A4';
$string['pageletter'] = 'Letter';
$string['pagenumber'] = 'Page {$a}';
$string['pdfoptions'] = 'PDF settings';
$string['plugindescription'] = 'Download text answers and attachment files submitted in response to essay questions in a quiz.';
$string['pluginname'] = 'Essay responses downloader plugin (quiz_essaydownload)';
$string['presentedto'] = 'Presented to: {$a}';
$string['privacy:metadata'] = 'The quiz essay download plugin does not store any personal data about any user.';
$string['response'] = 'Response';
$string['responsewith'] = 'Response to Question {$a}';
$string['shortennames'] = 'Shorten archive name and subfolder names.';
$string['shortennames_help'] = 'If the total path name of an extracted file is longer than 260 characters, this may cause problems with Windows\' built-in extraction tool. In this case, activating this checkbox may help. It might, however, make it more difficult to identify your students, if they have very long names.';
$string['source'] = 'Text source to use';
$string['source_help'] = 'If the question text and/or the student\'s response is written in HTML format, Moodle will automatically generate a plain-text summary of the formatted text. That summary will have all HTML tags removed and some basic formatting applied (e. g. headings and bold font transformed to ALL CAPS).<br><br>When generating PDF files, you can choose whether you want to use that summary or the original question text / student answer with its formatting. If you choose the summary, you should probably use a monospaced font as well.<br><br>Note that you cannot use the formatted original text when generating TXT files. Also note that the setting will not have any effect if the student was asked to write their answer in non-HTML format, e. g. plain-text.';
$string['sourceoriginal'] = 'Original HTML formatted text';
$string['sourcesummary'] = 'Plain-text summary';
$string['statistics'] = 'Statistics';
$string['statisticsnote'] = '{$a->words} words, {$a->chars} characters (not counting spaces)';
$string['troubleshooting'] = 'Troubleshooting';
$string['useflatarchive'] = 'Use flat folder hierarchy in archive';
$string['useflatarchive_help'] = 'If this option is checked, the archive\'s folder hierarchy will be "flatter", i. e. instead of having <i>Attempt_X/Question_Y/response.pdf</i> you will have <i>Attempt_X/Question_Y_response.pdf</i> (or similar if grouped by question). As a consequence, you will need fewer clicks to get your documents.';
