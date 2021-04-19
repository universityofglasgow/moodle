# Kprime (ETH)

## What it is:
A four option multiple true-false question type for moodle, as introduced by Krebs (1997). Kprime questions consist of an item stem and four corresponding statements or options. For each option students have to decide whether it is "true" or "false". Three different scoring methods are available: “Kprime”, where the student receives one point if all responses are correct, half a point if all save one response are correct, and zero points otherwise; “Kprime 1/0”, where the student receives one point if all responses are correct, and zero points otherwise; and “subpoints”, where the student is awarded subpoints for each correct response.

## Installation:
1. Extract the contents of the downloaded zip to `question/type/`.
1. Rename the extracted folder to `kprime`.
1. Start the Moodle upgrade procedure.

## Further information:
### Behat- and Unit tests:
Behat tests are included but scenarios are designed explicitly for ETH Zürich testcases.
Some of the included test steps are designed to work with the ETH Zürich Moodle setup.
However unit tests can be used in combination with any Moodle setup.
 
## Contributors:
ETH Zürich (Lead maintainer)
Thomas Korner (Service owner, thomas.korner@let.ethz.ch)