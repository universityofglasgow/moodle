## 3.3.6 ##

* Now displays hidden courses (to Teachers et al) as 'dimmed'
* Layout a bit better on legacy Bootstrap 2 themes (e.g. Clean).

## 3.3.5 ##

* Some support for bootstrapbase themes added
* favourites shown with correct 'star' in course list

## 3.3.4 ##

* Layout of courses improved using Bootstrap grids
* Option to keep favourites in 'normal' course list added to block settings
* Option to have a fixed sort order for courses tab
* Code tidied up somewhat

## 3.3.3 ##

* All javascript changed from YUI to jquery amd modules
* Rendering now using mustache templates
* Course overviews displayed by clicking icon 
* Favourites tab added
* Limit on courses displayed and child course display dropped.
* print_overview() still in use (for now)

## 3.3.1 ##

* Debugging messages raised by using deprecated `print_overview()` are not
  displayed now (the better fix would be to actually remove these calls, but it
  has to wait for the new maintainer).

## 3.3.0 ##

* Initial release of this block as a standalone plugin after being removed from
  Moodle core.
