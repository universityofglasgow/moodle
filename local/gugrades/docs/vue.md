# Vue.js

The [Vue.js](https://vuejs.org/) Javascript framework has been used to implement the user interface of the project. Vue requires a build stage and this is handled by Vite.
Vite is the default build application as described in the Vue docs.

The Vue portion lives entirely within the 'ui/' subdirectory.

Note that Vue requires a build step to create its final code. This is normally done using a built-in node-based web server but as we are already have Moodle running under a web server,
this has been done slightly differently. The package.json file has an additional 'command' added, 'watch' this runs the Vue production build step but still watches for code changes -
that is, whenever Vue javascript is saved the package will be rebuilt.

In order to access this, on the command line cd to the ui/ directory and run.

```
npm run watch
```

...this should build the Vue application and then sit and watch for changes.

This builds the Vue application and (assuming no errors) creates output in the ui/dist/ directory. We depart here from normal Vue practice to make this work with Moodle. The highlights are
as follows:
* The Moodle index.php (which the Moodle menu links to) is stored in the ui/dist/ directory. In order to prevent this being overridden on build (as would happen in 'normal' Vue), the
vite.config.js file has been modified to specify 'emptyOutDir' as false.
* The entire js data is minified into the file ui/dist/assets/entry.js. To get this into our Moodle application, this is 'included' within the index.php file
by 'echoing' a script statement. A shameless bodge.
* Similarly, the custom CSS fom the Vue application is output to ui/dist/style.css and is included within the index.php page.
* The remainder of the index.php file is just standard Moodle.

## Accessing Moodle resources

See [amd.md](./amd.md) for full description

## References

* [Vue Toatification](https://vue-toastification.maronato.dev/) is used to render notifications
* [FormKit](https://formkit.com/) is used to render form elements
* [Vue3 Easy Data Table](https://github.com/HC200ok/vue3-easy-data-table) used to render tables