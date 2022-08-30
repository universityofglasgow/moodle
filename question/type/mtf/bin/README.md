#qtype_multichoice Migration > qtype_mtf

######Description:
The Script mig_multichoice_to_mtf.php migrates questions of the type 
qtype_multichoice to the questiontype qtype_mtf. No questions will 
be overwritten or deleted, the script will solely create new questions.

######Required Parameters (choose 1):
 - courseid (values: a valid course ID)
 - categoryid (values: a valid category ID)
 - all (values: 1)

######Conditional Parameters (choose 0-n):
 - dryrun (values: 0,1)
 - migratesingleanswer (values: 0,1)
 - includesubcategories (values: 0,1)

  The Dryrun Option is enabled (1) by default.
  With Dryrun enabled no changes will be made to the database.
  Use Dryrun to receive information about possible issues before 
  migrating.

  The MigrateSingleAnswer Option is disabled (0) by default.
  With migratesingleanswer enabled those Multichoice Questions 
  with only one correct option are included into the Migration 
  to MTF as well.

  includesubcategories wird in Kombination mit Migration by 
  "categoryid" verwendet.
  Falls aktiviert (1) werden Unterkategorien mit migriert.

######Examples

 - Migrate MTF Questions in a specific course:
   ```
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?courseid=55
   ```
 - Migrate MTF Questions in a specific category:
   ```
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?categoryid=1
   ```
 - Migrate all MTF Questions:
    ```
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1
   ```
 - Disable Dryrun:
   ```
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1&dryrun=0
   ```
 - Enable MigrateSingleAnswer:
   ```
   MOODLE_URL/question/type/mtf/bin/mig_multichoice_to_mtf.php?all=1&dryrun=0&migratesingleanswer=1
   ```




#qtype_mtf Migration > qtype_multichoice#

######Description:
The Script mig_multichoice_to_mtf.php migrates questions of the type 
qtype_mtf to the questiontype qtype_mtf. No questions will be overwritten 
or deleted, the script will solely create new questions.

######Required Parameters (choose 1):
 - courseid (values: a valid course ID)
 - categoryid (values: a valid category ID)
 - all (values: 1)

######Conditional Parameters (choose 0-n):
 - dryrun (values: 0,1)
 - autoweights (values: 0,1)
 - includesubcategories (values: 0,1)

  The Dryrun Option is enabled (1) by default.
  With Dryrun enabled no changes will be made to the database.
  Use Dryrun to receive information about possible issues before 
  migrating.

  The Autoweights Options is disabled (0) by default.
  While migrating from MTF to Multichoice, grades for correct or 
  incorrect answers are usually set equal. However in some cases 
  the SUM of all grades does not match 100%. With Autoweights enabled 
  different grades will be set to match a SUM of 100%. With Autoweights 
  disabled the affected question will be ignored in migration.

  includesubcategories is used in combination with migration by categoryid.
  If enabled all subcategories will be migrated as well.

######Examples

 - Migrate MTF Questions in a specific course:
   ```
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?courseid=55
  ```
 - Migrate MTF Questions in a specific category:
   ```
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?categoryid=1
   ```
 - Migrate all MTF Questions:
   ```
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?all=1
   ```
 - Disable Dryrun:
   ```
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?all=1&dryrun=0
   ```
 - Enable AutoWeights:
   ```
   MOODLE_URL/question/type/mtf/bin/mig_mtf_to_multichoice.php?all=1&dryrun=0&autoweights=1
   ```