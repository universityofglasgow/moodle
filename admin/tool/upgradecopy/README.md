# Upgrade Copy Generator

Simple plugin to generate a list of 'cp' commands for all optional plugins 
within your Moodle site. 

Purpose is to copy all the folders from an old Moodle site to a new, empty
one. Making transferring plugins a bit easier

## Installation

Install as an admin tool so that it ends up in admin/tool/upgradecopy. Then go to site admin
to complete installation

## Use

Find at Site admin > Development > Upgradecopy

You supply the prefix for the 'from' side of the cp and the 'to' side. This will typically be 
the paths to the root of your source and destination Moodle sites. You may need to experiment a bit.

NOTE: The plugin only generates a list. It doesn't do anything to either of your sites. 

You can then copy and paste the generated commands to copy the directories.
