/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=script&setup=true&lang=js":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=script&setup=true&lang=js ***!
  \***********************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n/* harmony import */ var _components_ActivityTree_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/components/ActivityTree.vue */ \"./src/components/ActivityTree.vue\");\n/* harmony import */ var _components_MString_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/components/MString.vue */ \"./src/components/MString.vue\");\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'ActivitySelect',\n  props: {\n    categoryid: Number\n  },\n  emits: ['activityselected'],\n\n  setup(__props, {\n    expose,\n    emit\n  }) {\n    expose();\n    const props = __props;\n    const activitytree = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)({});\n    const categoryname = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)('');\n    const selectedactivity = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)({});\n    const loaded = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)(false);\n    const collapsed = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)(false); // Get the sub-category / activity\n\n    function getActivity() {\n      const GU = window.GU;\n      const courseid = GU.courseid;\n      const fetchMany = GU.fetchMany;\n      const catid = props.categoryid;\n      fetchMany([{\n        methodname: 'local_gugrades_get_activities',\n        args: {\n          courseid: courseid,\n          categoryid: catid\n        }\n      }])[0].then(result => {\n        const tree = JSON.parse(result['activities']);\n        activitytree.value = tree;\n        categoryname.value = tree.category.fullname;\n        loaded.value = true;\n      }).catch(error => {\n        window.console.log(error);\n      });\n    } // Get the selected avtivity\n\n\n    function activity_selected(activityid) {\n      const GU = window.GU;\n      const fetchMany = GU.fetchMany;\n      fetchMany([{\n        methodname: 'local_gugrades_get_grade_item',\n        args: {\n          itemid: activityid\n        }\n      }])[0].then(result => {\n        selectedactivity.value = result;\n        collapsed.value = true;\n      }).catch(error => {\n        window.console.log(error);\n      }); // Emit id as well\n\n      emit('activityselected', activityid);\n    } // (Re-)open the selection\n\n\n    function open_selection() {\n      collapsed.value = false;\n    }\n\n    (0,vue__WEBPACK_IMPORTED_MODULE_0__.onMounted)(() => {\n      getActivity();\n    }); // If the categoryid prop changes then we read new values\n    // and (re-)open the dialogue\n\n    (0,vue__WEBPACK_IMPORTED_MODULE_0__.watch)(() => props.categoryid, () => {\n      collapsed.value = false;\n      getActivity();\n    });\n    const __returned__ = {\n      props,\n      emit,\n      activitytree,\n      categoryname,\n      selectedactivity,\n      loaded,\n      collapsed,\n      getActivity,\n      activity_selected,\n      open_selection,\n      ref: vue__WEBPACK_IMPORTED_MODULE_0__.ref,\n      onMounted: vue__WEBPACK_IMPORTED_MODULE_0__.onMounted,\n      watch: vue__WEBPACK_IMPORTED_MODULE_0__.watch,\n      ActivityTree: _components_ActivityTree_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n      MString: _components_MString_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivitySelect.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivityTree.vue?vue&type=script&setup=true&lang=js":
/*!*********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivityTree.vue?vue&type=script&setup=true&lang=js ***!
  \*********************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'ActivityTree',\n  props: {\n    nodes: Object\n  },\n  emits: ['activityselected'],\n\n  setup(__props, {\n    expose,\n    emit\n  }) {\n    expose();\n    const props = __props; // Emit activity id when activity selected\n\n    function activity_click(itemid, event) {\n      event.preventDefault();\n      emit('activityselected', itemid);\n    } // As emit only works for one level, this re-emits events\n    // from lower levels.\n\n\n    function sub_activity_click(activityid) {\n      emit('activityselected', activityid);\n    }\n\n    const __returned__ = {\n      props,\n      emit,\n      activity_click,\n      sub_activity_click\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivityTree.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/CaptureTable.vue?vue&type=script&setup=true&lang=js":
/*!*********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/CaptureTable.vue?vue&type=script&setup=true&lang=js ***!
  \*********************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js\");\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/runtime-core/dist/runtime-core.esm-bundler.js\");\n/* harmony import */ var _components_NameFilter_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/components/NameFilter.vue */ \"./src/components/NameFilter.vue\");\n/* harmony import */ var _components_PagingBar_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/components/PagingBar.vue */ \"./src/components/PagingBar.vue\");\n/* harmony import */ var _js_getstrings_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/js/getstrings.js */ \"./src/js/getstrings.js\");\n\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'CaptureTable',\n  props: {\n    itemid: Number\n  },\n\n  setup(__props, {\n    expose\n  }) {\n    expose();\n    const props = __props;\n    const PAGESIZE = 20;\n    const users = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.ref)([]);\n    const pagedusers = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.ref)([]);\n    const strings = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.ref)({});\n    const totalrows = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.ref)(0);\n    const perpage = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.ref)(PAGESIZE);\n    const currentpage = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.ref)(1);\n    let firstname = '';\n    let lastname = '';\n    /**\n     * filter out paged users\n     */\n\n    function get_pagedusers() {\n      const first = (currentpage.value - 1) * PAGESIZE;\n      const last = first + PAGESIZE - 1;\n      pagedusers.value = [];\n\n      for (let i = first; i <= last; i++) {\n        pagedusers.value.push(users.value[i]);\n      }\n    }\n    /**\n     * Get filtered/paged data\n     */\n\n\n    function get_page_data(itemid, first, last) {\n      const GU = window.GU;\n      const courseid = GU.courseid;\n      const fetchMany = GU.fetchMany;\n      fetchMany([{\n        methodname: 'local_gugrades_get_capture_page',\n        args: {\n          courseid: courseid,\n          gradeitemid: itemid,\n          pageno: 0,\n          pagelength: 0,\n          firstname: first,\n          lastname: last\n        }\n      }])[0].then(result => {\n        users.value = JSON.parse(result['users']);\n        totalrows.value = users.value.length;\n        get_pagedusers();\n      }).catch(error => {\n        window.console.log(error);\n      });\n    }\n    /**\n     * Firstname/lastname filter selected\n     * @param {*} first \n     * @param {*} last \n     */\n\n\n    function filter_selected(first, last) {\n      window.console.log('FILTER ', first, last);\n\n      if (first == 'all') {\n        first = '';\n      }\n\n      if (last == 'all') {\n        last = '';\n      }\n\n      firstname = first;\n      lastname = last;\n      window.console.log('FILTER 2', firstname, lastname);\n      get_page_data(props.itemid, first, last);\n    }\n    /**\n     * Page selected on paging bar\n     */\n\n\n    function pagechanged(page) {\n      currentpage.value = page;\n      get_pagedusers();\n    }\n\n    const showtable = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_4__.computed)(() => {\n      return users.value.length != 0;\n    });\n    (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_4__.watch)(() => props.itemid, itemid => {\n      get_page_data(itemid, firstname, lastname);\n    });\n    (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_4__.onMounted)(() => {\n      // Get the moodle strings for this page\n      const stringslist = ['addgrade', 'awaitingcapture', 'firstnamelastname', 'idnumber', 'nothingtodisplay', 'grade'];\n      (0,_js_getstrings_js__WEBPACK_IMPORTED_MODULE_2__.getstrings)(stringslist).then(results => {\n        Object.keys(results).forEach(name => {\n          strings.value[name] = results[name];\n        });\n      }); // Get the data for the table\n\n      get_page_data(props.itemid, firstname, lastname);\n    });\n    const __returned__ = {\n      PAGESIZE,\n      props,\n      users,\n      pagedusers,\n      strings,\n      totalrows,\n      perpage,\n      currentpage,\n      firstname,\n      lastname,\n      get_pagedusers,\n      get_page_data,\n      filter_selected,\n      pagechanged,\n      showtable,\n      ref: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.ref,\n      defineProps: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_4__.defineProps,\n      computed: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_4__.computed,\n      watch: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_4__.watch,\n      onMounted: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_4__.onMounted,\n      NameFilter: _components_NameFilter_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n      PagingBar: _components_PagingBar_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n      getstrings: _js_getstrings_js__WEBPACK_IMPORTED_MODULE_2__.getstrings\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/components/CaptureTable.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/InitialBar.vue?vue&type=script&setup=true&lang=js":
/*!*******************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/InitialBar.vue?vue&type=script&setup=true&lang=js ***!
  \*******************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js\");\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/runtime-core/dist/runtime-core.esm-bundler.js\");\n/* harmony import */ var _components_MString_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/components/MString.vue */ \"./src/components/MString.vue\");\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'InitialBar',\n  props: {\n    'label': String,\n    'selected': String\n  },\n  emits: ['selected'],\n\n  setup(__props, {\n    expose,\n    emit\n  }) {\n    expose();\n    const props = __props;\n    const activeletter = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.ref)('all');\n    const letters = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.computed)(() => {\n      return Array.from(\"ABCDEFGHIJKLMNOPQRSTUVWXYZ\");\n    });\n\n    function letterclicked(letter, event) {\n      event.preventDefault();\n      activeletter.value = letter;\n      emit('selected', letter);\n    }\n\n    function is_active(letter) {\n      return activeletter.value == letter;\n    }\n\n    (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.watch)(() => props.selected, selected => {\n      activeletter.value = selected;\n      emit('selected', activeletter.value);\n    });\n    const __returned__ = {\n      props,\n      emit,\n      activeletter,\n      letters,\n      letterclicked,\n      is_active,\n      ref: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.ref,\n      computed: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.computed,\n      defineProps: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.defineProps,\n      defineEmits: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.defineEmits,\n      watch: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.watch,\n      MString: _components_MString_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/components/InitialBar.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/LevelOneSelect.vue?vue&type=script&setup=true&lang=js":
/*!***********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/LevelOneSelect.vue?vue&type=script&setup=true&lang=js ***!
  \***********************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _components_MString_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/components/MString.vue */ \"./src/components/MString.vue\");\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js\");\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/runtime-core/dist/runtime-core.esm-bundler.js\");\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'LevelOneSelect',\n  emits: ['levelchange'],\n\n  setup(__props, {\n    expose,\n    emit\n  }) {\n    expose();\n    const level1categories = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.ref)([]);\n    const notsetup = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.ref)(false); // Get the top level categories\n\n    function getLevelOne() {\n      const GU = window.GU;\n      const courseid = GU.courseid;\n      const fetchMany = GU.fetchMany;\n      fetchMany([{\n        methodname: 'local_gugrades_get_levelonecategories',\n        args: {\n          courseid\n        }\n      }])[0].then(result => {\n        level1categories.value = result;\n\n        if (result.length == 0) {\n          notsetup.value = true;\n        }\n\n        window.console.log(result);\n      }).catch(error => {\n        window.console.log(error);\n      });\n    } // Handle change of selection in dropdown.\n\n\n    function levelOneChange(event) {\n      const categoryid = event.target.value;\n      emit('levelchange', categoryid);\n    }\n\n    (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.onMounted)(() => {\n      getLevelOne();\n    });\n    const __returned__ = {\n      level1categories,\n      notsetup,\n      emit,\n      getLevelOne,\n      levelOneChange,\n      MString: _components_MString_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n      ref: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.ref,\n      onMounted: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.onMounted,\n      defineEmits: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.defineEmits\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/components/LevelOneSelect.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/MString.vue?vue&type=script&setup=true&lang=js":
/*!****************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/MString.vue?vue&type=script&setup=true&lang=js ***!
  \****************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'MString',\n  props: {\n    name: String,\n    component: String\n  },\n\n  setup(__props, {\n    expose\n  }) {\n    expose();\n    const props = __props;\n    const moodlestring = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)('');\n    (0,vue__WEBPACK_IMPORTED_MODULE_0__.onMounted)(() => {\n      const GU = window.GU; // Default component is local_gugrades (this one)\n\n      const finalcomponent = !props.component ? 'local_gugrades' : props.component;\n      const strings = [{\n        key: props.name,\n        component: finalcomponent\n      }];\n      GU.getStrings(strings).then(result => {\n        moodlestring.value = result[0];\n      });\n    });\n    const __returned__ = {\n      props,\n      moodlestring,\n      ref: vue__WEBPACK_IMPORTED_MODULE_0__.ref,\n      onMounted: vue__WEBPACK_IMPORTED_MODULE_0__.onMounted\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/components/MString.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/NameFilter.vue?vue&type=script&setup=true&lang=js":
/*!*******************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/NameFilter.vue?vue&type=script&setup=true&lang=js ***!
  \*******************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js\");\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/runtime-core/dist/runtime-core.esm-bundler.js\");\n/* harmony import */ var _components_InitialBar_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/components/InitialBar.vue */ \"./src/components/InitialBar.vue\");\n/* harmony import */ var _js_getstrings_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/js/getstrings.js */ \"./src/js/getstrings.js\");\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'NameFilter',\n  emits: ['selected'],\n\n  setup(__props, {\n    expose,\n    emit\n  }) {\n    expose();\n    const first = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.ref)('all');\n    const last = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.ref)('all');\n    const strings = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.ref)({});\n    /**\n     * Process letter selected in one of the bars\n     */\n\n    function first_selected(letter) {\n      first.value = letter;\n      emit('selected', first.value, last.value);\n    }\n\n    function last_selected(letter) {\n      last.value = letter;\n      emit('selected', first.value, last.value);\n    }\n    /**\n     * Reset filter back to all/all\n     */\n\n\n    function reset_filter() {\n      first.value = 'all';\n      last.value = 'all';\n    }\n\n    (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.onMounted)(() => {\n      const stringslist = ['firstname', 'lastname', 'resetfilter'];\n      (0,_js_getstrings_js__WEBPACK_IMPORTED_MODULE_1__.getstrings)(stringslist).then(results => {\n        Object.keys(results).forEach(name => {\n          strings.value[name] = results[name];\n        });\n      });\n    });\n    const __returned__ = {\n      emit,\n      first,\n      last,\n      strings,\n      first_selected,\n      last_selected,\n      reset_filter,\n      ref: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.ref,\n      defineEmits: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.defineEmits,\n      onMounted: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_3__.onMounted,\n      InitialBar: _components_InitialBar_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n      getstrings: _js_getstrings_js__WEBPACK_IMPORTED_MODULE_1__.getstrings\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/components/NameFilter.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/PagingBar.vue?vue&type=script&setup=true&lang=js":
/*!******************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/PagingBar.vue?vue&type=script&setup=true&lang=js ***!
  \******************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n // Number of pages to show either side of current\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'PagingBar',\n  props: {\n    totalrows: Number,\n    perpage: Number,\n    currentpage: Number\n  },\n  emits: ['pagechange'],\n\n  setup(__props, {\n    expose,\n    emit\n  }) {\n    expose();\n    const props = __props;\n    const PAGES_EITHERSIDE = 4;\n    const show = (0,vue__WEBPACK_IMPORTED_MODULE_0__.reactive)({\n      previous: false,\n      previouspage: 0,\n      first: false,\n      last: false,\n      next: false,\n      nextpage: 0\n    });\n    const pages = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)([]);\n    const activepage = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)(1);\n    const pagecount = (0,vue__WEBPACK_IMPORTED_MODULE_0__.ref)(0);\n\n    function is_active(page) {\n      return page == activepage.value;\n    }\n    /**\n     * Calculate the pages and various show options\n     * given current page\n     */\n\n\n    function get_pages() {\n      pagecount.value = Math.ceil(props.totalrows / props.perpage);\n      let lower = activepage.value - PAGES_EITHERSIDE;\n\n      if (lower < 1) {\n        lower = 1;\n      }\n\n      let upper = activepage.value + PAGES_EITHERSIDE;\n\n      if (upper > pagecount.value) {\n        upper = pagecount.value;\n      }\n\n      show.previous = lower > 1;\n      show.previouspage = activepage.value - 1;\n      show.next = upper < pagecount.value;\n      show.nextpage = activepage.value + 1;\n      show.first = activepage.value > PAGES_EITHERSIDE + 1;\n      show.last = activepage.value < pagecount.value - PAGES_EITHERSIDE;\n      pages.value = [];\n\n      for (let i = lower; i <= upper; i++) {\n        pages.value.push(i);\n      }\n\n      emit('pagechange', activepage.value);\n    }\n    /**\n     * Watch for number of rows changing (when data acquired)\n     */\n\n\n    (0,vue__WEBPACK_IMPORTED_MODULE_0__.watch)(() => props.totalrows, () => {\n      get_pages();\n    });\n    /**\n     * Watch for current page change\n     *\n     */\n\n    (0,vue__WEBPACK_IMPORTED_MODULE_0__.watch)(() => props.currentpage, () => {\n      activepage.value = props.currentpage;\n      get_pages();\n    });\n    /**\n     * Page number has been clicked\n     */\n\n    function pageclick(page) {\n      activepage.value = page;\n      get_pages();\n    }\n\n    const __returned__ = {\n      PAGES_EITHERSIDE,\n      props,\n      emit,\n      show,\n      pages,\n      activepage,\n      pagecount,\n      is_active,\n      get_pages,\n      pageclick,\n      ref: vue__WEBPACK_IMPORTED_MODULE_0__.ref,\n      reactive: vue__WEBPACK_IMPORTED_MODULE_0__.reactive,\n      watch: vue__WEBPACK_IMPORTED_MODULE_0__.watch\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/components/PagingBar.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/TabsNav.vue?vue&type=script&setup=true&lang=js":
/*!****************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/TabsNav.vue?vue&type=script&setup=true&lang=js ***!
  \****************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js\");\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/runtime-core/dist/runtime-core.esm-bundler.js\");\n/* harmony import */ var _components_MString_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/components/MString.vue */ \"./src/components/MString.vue\");\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'TabsNav',\n  emits: ['tabchange'],\n\n  setup(__props, {\n    expose,\n    emit\n  }) {\n    expose();\n    const isCapture = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.ref)(true);\n    /**\n     * Detect change of tab and emit result to parent\n     * @param {} item \n     */\n\n    function clickTab(item) {\n      if (item == 'aggregate') {\n        isCapture.value = false;\n        emit('tabchange', 'aggregate');\n      } else {\n        isCapture.value = true;\n        emit('tabchange', 'capture');\n      }\n    }\n\n    const __returned__ = {\n      isCapture,\n      emit,\n      clickTab,\n      ref: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.ref,\n      defineEmits: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.defineEmits,\n      MString: _components_MString_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/components/TabsNav.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/CaptureAggregation.vue?vue&type=script&setup=true&lang=js":
/*!**********************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/CaptureAggregation.vue?vue&type=script&setup=true&lang=js ***!
  \**********************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js\");\n/* harmony import */ var _components_MString_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/components/MString.vue */ \"./src/components/MString.vue\");\n/* harmony import */ var _components_LevelOneSelect_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/components/LevelOneSelect.vue */ \"./src/components/LevelOneSelect.vue\");\n/* harmony import */ var _components_TabsNav_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/components/TabsNav.vue */ \"./src/components/TabsNav.vue\");\n/* harmony import */ var _components_ActivitySelect_vue__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/components/ActivitySelect.vue */ \"./src/components/ActivitySelect.vue\");\n/* harmony import */ var _components_CaptureTable_vue__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/components/CaptureTable.vue */ \"./src/components/CaptureTable.vue\");\n\n\n\n\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'CaptureAggregation',\n\n  setup(__props, {\n    expose\n  }) {\n    expose();\n    const currenttab = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_5__.ref)('capture');\n    const level1category = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_5__.ref)(0);\n    const showactivityselect = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_5__.ref)(false);\n    const itemid = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_5__.ref)(0);\n    /**\n     * Capture change to top level category dropdown\n     * @param {*} level \n     */\n\n    function levelOneChange(level) {\n      itemid.value = 0;\n      level1category.value = parseInt(level);\n\n      if (level1category.value) {\n        showactivityselect.value = true;\n      } else {\n        showactivityselect.value = false;\n      }\n    }\n    /**\n     * Capture change to activity selection\n     */\n\n\n    function activity_selected(newitemid) {\n      itemid.value = newitemid;\n    }\n    /**\n     * Capture change to capture/aggregate tab\n     * @param {*} tab \n     */\n\n\n    function tabChange(tab) {\n      currenttab.value = tab;\n    }\n\n    const __returned__ = {\n      currenttab,\n      level1category,\n      showactivityselect,\n      itemid,\n      levelOneChange,\n      activity_selected,\n      tabChange,\n      ref: _vue_runtime_core__WEBPACK_IMPORTED_MODULE_5__.ref,\n      MString: _components_MString_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n      LevelOneSelect: _components_LevelOneSelect_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"],\n      TabsNav: _components_TabsNav_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"],\n      ActivitySelect: _components_ActivitySelect_vue__WEBPACK_IMPORTED_MODULE_3__[\"default\"],\n      CaptureTable: _components_CaptureTable_vue__WEBPACK_IMPORTED_MODULE_4__[\"default\"]\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/views/CaptureAggregation.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/App.vue?vue&type=template&id=7ba5bd90":
/*!******************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/App.vue?vue&type=template&id=7ba5bd90 ***!
  \******************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nfunction render(_ctx, _cache) {\n  const _component_router_view = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"router-view\");\n\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)(_component_router_view);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/App.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=template&id=09ba6d9b&scoped=true":
/*!****************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=template&id=09ba6d9b&scoped=true ***!
  \****************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\n\nconst _withScopeId = n => ((0,vue__WEBPACK_IMPORTED_MODULE_0__.pushScopeId)(\"data-v-09ba6d9b\"), n = n(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.popScopeId)(), n);\n\nconst _hoisted_1 = {\n  key: 0,\n  class: \"mt-2 border border-dark p-3 rounded\"\n};\nconst _hoisted_2 = {\n  class: \"col-10\"\n};\n\nconst _hoisted_3 = /*#__PURE__*/_withScopeId(() => /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", {\n  class: \"col-2 text-right\"\n}, [/*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"i\", {\n  class: \"fa fa-chevron-down\",\n  \"aria-hidden\": \"true\"\n})], -1\n/* HOISTED */\n));\n\nconst _hoisted_4 = {\n  key: 1\n};\n\nconst _hoisted_5 = /*#__PURE__*/_withScopeId(() => /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"i\", {\n  class: \"fa fa-list-alt\",\n  \"aria-hidden\": \"true\"\n}, null, -1\n/* HOISTED */\n));\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return $setup.loaded ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [$setup.collapsed ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", {\n    key: 0,\n    onClick: $setup.open_selection,\n    class: \"cursor-pointer row\"\n  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"MString\"], {\n    name: \"selected\"\n  }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(\": \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.selectedactivity.itemname), 1\n  /* TEXT */\n  )]), _hoisted_3])) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_4, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"b\", null, [_hoisted_5, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(\" \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.categoryname), 1\n  /* TEXT */\n  )]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"ActivityTree\"], {\n    nodes: $setup.activitytree,\n    onActivityselected: $setup.activity_selected\n  }, null, 8\n  /* PROPS */\n  , [\"nodes\"])]))])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivitySelect.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivityTree.vue?vue&type=template&id=75d4e0fd":
/*!**************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivityTree.vue?vue&type=template&id=75d4e0fd ***!
  \**************************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nconst _hoisted_1 = {\n  class: \"list-unstyled pl-3\"\n};\nconst _hoisted_2 = [\"onClick\"];\n\nconst _hoisted_3 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"i\", {\n  class: \"fa fa-list-alt\",\n  \"aria-hidden\": \"true\"\n}, null, -1\n/* HOISTED */\n);\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_ActivityTree = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"ActivityTree\", true);\n\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"ul\", _hoisted_1, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($setup.props.nodes.items, item => {\n    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", {\n      key: item.id\n    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n      href: \"#\",\n      onClick: $event => $setup.activity_click(item.id, $event)\n    }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(item.itemname), 9\n    /* TEXT, PROPS */\n    , _hoisted_2)]);\n  }), 128\n  /* KEYED_FRAGMENT */\n  )), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($setup.props.nodes.categories, category => {\n    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", {\n      key: category.id\n    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"b\", null, [_hoisted_3, (0,vue__WEBPACK_IMPORTED_MODULE_0__.createTextVNode)(\" \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(category.category.fullname), 1\n    /* TEXT */\n    )]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_ActivityTree, {\n      nodes: category,\n      onActivityselected: $setup.sub_activity_click\n    }, null, 8\n    /* PROPS */\n    , [\"nodes\"])]);\n  }), 128\n  /* KEYED_FRAGMENT */\n  ))]);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivityTree.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/CaptureTable.vue?vue&type=template&id=90df3f50":
/*!**************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/CaptureTable.vue?vue&type=template&id=90df3f50 ***!
  \**************************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nconst _hoisted_1 = {\n  class: \"table-responsive\"\n};\nconst _hoisted_2 = {\n  key: 0,\n  class: \"table table-striped table-sm mt-4 border rounded\"\n};\nconst _hoisted_3 = {\n  class: \"thead-light\"\n};\n\nconst _hoisted_4 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"th\", null, null, -1\n/* HOISTED */\n);\n\nconst _hoisted_5 = {\n  type: \"button\",\n  class: \"btn btn-outline-primary btn-sm\"\n};\nconst _hoisted_6 = {\n  key: 0\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"NameFilter\"], {\n    onSelected: $setup.filter_selected\n  }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"PagingBar\"], {\n    totalrows: $setup.totalrows,\n    perpage: $setup.perpage,\n    currentpage: $setup.currentpage,\n    onPagechange: $setup.pagechanged\n  }, null, 8\n  /* PROPS */\n  , [\"totalrows\", \"perpage\", \"currentpage\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_1, [$setup.showtable ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"table\", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"thead\", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"th\", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.strings.firstnamelastname), 1\n  /* TEXT */\n  ), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"th\", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.strings.idnumber), 1\n  /* TEXT */\n  ), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"th\", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.strings.grade), 1\n  /* TEXT */\n  ), _hoisted_4]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"tbody\", null, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($setup.pagedusers, user => {\n    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"tr\", {\n      key: user.id\n    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"td\", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(user.firstname) + \" \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(user.lastname), 1\n    /* TEXT */\n    ), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"td\", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(user.idnumber), 1\n    /* TEXT */\n    ), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"td\", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.strings.awaitingcapture), 1\n    /* TEXT */\n    ), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"td\", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"button\", _hoisted_5, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.strings.addgrade), 1\n    /* TEXT */\n    )])]);\n  }), 128\n  /* KEYED_FRAGMENT */\n  ))])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"PagingBar\"], {\n    totalrows: $setup.totalrows,\n    perpage: $setup.perpage,\n    currentpage: $setup.currentpage,\n    onPagechange: $setup.pagechanged\n  }, null, 8\n  /* PROPS */\n  , [\"totalrows\", \"perpage\", \"currentpage\"])]), !$setup.showtable ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"h2\", _hoisted_6, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.strings.nothingtodisplay), 1\n  /* TEXT */\n  )) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true)]);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/CaptureTable.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/InitialBar.vue?vue&type=template&id=116cbe42":
/*!************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/InitialBar.vue?vue&type=template&id=116cbe42 ***!
  \************************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nconst _hoisted_1 = {\n  class: \"initialbar {{class}} d-flex flex-wrap justify-content-center justify-content-md-start\"\n};\nconst _hoisted_2 = {\n  class: \"initialbarlabel mr-2\"\n};\nconst _hoisted_3 = {\n  class: \"initialbargroups d-flex flex-wrap justify-content-center justify-content-md-start\"\n};\nconst _hoisted_4 = {\n  class: \"pagination pagination-sm\"\n};\nconst _hoisted_5 = {\n  class: \"pagination pagination-sm\"\n};\nconst _hoisted_6 = [\"onClick\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_2, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.props.label), 1\n  /* TEXT */\n  ), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"nav\", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"ul\", _hoisted_4, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"li\", {\n    class: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)([\"initialbarall page-item\", {\n      active: $setup.is_active('all')\n    }])\n  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n    \"data-initial\": \"\",\n    class: \"page-link\",\n    href: \"#\",\n    onClick: _cache[0] || (_cache[0] = $event => $setup.letterclicked('all', $event))\n  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"MString\"], {\n    name: \"all\"\n  })])], 2\n  /* CLASS */\n  )]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"ul\", _hoisted_5, [((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($setup.letters, letter => {\n    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", {\n      key: letter,\n      class: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)([\"page-item\", {\n        active: $setup.is_active(letter)\n      }])\n    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n      class: \"page-link\",\n      href: \"#\",\n      onClick: $event => $setup.letterclicked(letter, $event)\n    }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(letter), 9\n    /* TEXT, PROPS */\n    , _hoisted_6)], 2\n    /* CLASS */\n    );\n  }), 128\n  /* KEYED_FRAGMENT */\n  ))])])]);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/InitialBar.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/LevelOneSelect.vue?vue&type=template&id=c5eed9e4":
/*!****************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/LevelOneSelect.vue?vue&type=template&id=c5eed9e4 ***!
  \****************************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nconst _hoisted_1 = {\n  key: 0,\n  class: \"alert alert-warning\"\n};\nconst _hoisted_2 = {\n  value: \"0\"\n};\nconst _hoisted_3 = [\"value\"];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", null, [$setup.notsetup ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"MString\"], {\n    name: \"notoplevel\"\n  })])) : ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"select\", {\n    key: 1,\n    class: \"form-control border-dark\",\n    onChange: _cache[0] || (_cache[0] = $event => $setup.levelOneChange($event))\n  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"option\", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"MString\"], {\n    name: \"selectgradecategory\"\n  })]), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($setup.level1categories, category => {\n    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"option\", {\n      key: category.id,\n      value: category.id\n    }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(category.fullname), 9\n    /* TEXT, PROPS */\n    , _hoisted_3);\n  }), 128\n  /* KEYED_FRAGMENT */\n  ))], 32\n  /* HYDRATE_EVENTS */\n  ))]);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/LevelOneSelect.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/MString.vue?vue&type=template&id=144c19de":
/*!*********************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/MString.vue?vue&type=template&id=144c19de ***!
  \*********************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"span\", null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.moodlestring), 1\n  /* TEXT */\n  );\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/MString.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/NameFilter.vue?vue&type=template&id=413b4e93":
/*!************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/NameFilter.vue?vue&type=template&id=413b4e93 ***!
  \************************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nconst _hoisted_1 = {\n  class: \"row mt-4\"\n};\nconst _hoisted_2 = {\n  class: \"col-5\"\n};\nconst _hoisted_3 = {\n  class: \"col-5\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"InitialBar\"], {\n    selected: $setup.first,\n    label: $setup.strings.firstname,\n    onSelected: $setup.first_selected\n  }, null, 8\n  /* PROPS */\n  , [\"selected\", \"label\"]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"InitialBar\"], {\n    selected: $setup.last,\n    label: $setup.strings.lastname,\n    onSelected: $setup.last_selected\n  }, null, 8\n  /* PROPS */\n  , [\"selected\", \"label\"])])]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"button\", {\n    class: \"btn btn-primary btn-small\",\n    onClick: $setup.reset_filter\n  }, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.strings.resetfilter), 1\n  /* TEXT */\n  )])]);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/NameFilter.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/PagingBar.vue?vue&type=template&id=02515d67":
/*!***********************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/PagingBar.vue?vue&type=template&id=02515d67 ***!
  \***********************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nconst _hoisted_1 = {\n  class: \"pagination pagination-centered justify-content-center\"\n};\nconst _hoisted_2 = {\n  class: \"mt-1 pagination\"\n};\nconst _hoisted_3 = {\n  key: 0,\n  class: \"page-item\"\n};\n\nconst _hoisted_4 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", {\n  \"aria-hidden\": \"true\"\n}, \"«\", -1\n/* HOISTED */\n);\n\nconst _hoisted_5 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", {\n  class: \"sr-only\"\n}, \"Previous page\", -1\n/* HOISTED */\n);\n\nconst _hoisted_6 = [_hoisted_4, _hoisted_5];\nconst _hoisted_7 = {\n  key: 1,\n  class: \"page-item\"\n};\n\nconst _hoisted_8 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", {\n  \"aria-hidden\": \"true\"\n}, \"1\", -1\n/* HOISTED */\n);\n\nconst _hoisted_9 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", {\n  class: \"sr-only\"\n}, \"Page 1\", -1\n/* HOISTED */\n);\n\nconst _hoisted_10 = [_hoisted_8, _hoisted_9];\nconst _hoisted_11 = {\n  key: 2,\n  class: \"page-item disabled\"\n};\n\nconst _hoisted_12 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", {\n  class: \"page-link\"\n}, \"…\", -1\n/* HOISTED */\n);\n\nconst _hoisted_13 = [_hoisted_12];\nconst _hoisted_14 = [\"onClick\"];\nconst _hoisted_15 = {\n  \"aria-hidden\": \"true\"\n};\nconst _hoisted_16 = {\n  class: \"sr-only\"\n};\nconst _hoisted_17 = {\n  key: 3,\n  class: \"page-item disabled\"\n};\n\nconst _hoisted_18 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", {\n  class: \"page-link\"\n}, \"…\", -1\n/* HOISTED */\n);\n\nconst _hoisted_19 = [_hoisted_18];\nconst _hoisted_20 = {\n  key: 4,\n  class: \"page-item\"\n};\nconst _hoisted_21 = {\n  \"aria-hidden\": \"true\"\n};\nconst _hoisted_22 = {\n  class: \"sr-only\"\n};\nconst _hoisted_23 = {\n  key: 5,\n  class: \"page-item\"\n};\n\nconst _hoisted_24 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", {\n  \"aria-hidden\": \"true\"\n}, \"»\", -1\n/* HOISTED */\n);\n\nconst _hoisted_25 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", {\n  class: \"sr-only\"\n}, \"Next page\", -1\n/* HOISTED */\n);\n\nconst _hoisted_26 = [_hoisted_24, _hoisted_25];\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"nav\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"ul\", _hoisted_2, [$setup.show.previous ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n    class: \"page-link\",\n    onClick: _cache[0] || (_cache[0] = $event => $setup.pageclick($setup.show.previouspage))\n  }, _hoisted_6)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), $setup.show.first ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", _hoisted_7, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n    class: \"page-link\",\n    onClick: _cache[1] || (_cache[1] = $event => $setup.pageclick(1))\n  }, _hoisted_10)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), $setup.show.first ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", _hoisted_11, _hoisted_13)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(true), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(vue__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,vue__WEBPACK_IMPORTED_MODULE_0__.renderList)($setup.pages, page => {\n    return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", {\n      key: page,\n      class: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)([\"page-item\", {\n        active: $setup.is_active(page)\n      }])\n    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n      href: \"#\",\n      class: \"page-link\",\n      onClick: $event => $setup.pageclick(page)\n    }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_15, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(page), 1\n    /* TEXT */\n    ), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_16, \"Page \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)(page), 1\n    /* TEXT */\n    )], 8\n    /* PROPS */\n    , _hoisted_14)], 2\n    /* CLASS */\n    );\n  }), 128\n  /* KEYED_FRAGMENT */\n  )), $setup.show.last ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", _hoisted_17, _hoisted_19)) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), $setup.show.last ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", _hoisted_20, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n    class: \"page-link\",\n    onClick: _cache[2] || (_cache[2] = $event => $setup.pageclick($setup.pagecount))\n  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_21, (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.pagecount), 1\n  /* TEXT */\n  ), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"span\", _hoisted_22, \"Page \" + (0,vue__WEBPACK_IMPORTED_MODULE_0__.toDisplayString)($setup.pagecount), 1\n  /* TEXT */\n  )])])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true), $setup.show.next ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"li\", _hoisted_23, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n    class: \"page-link\",\n    onClick: _cache[3] || (_cache[3] = $event => $setup.pageclick($setup.show.nextpage))\n  }, _hoisted_26)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true)])]);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/PagingBar.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/TabsNav.vue?vue&type=template&id=5802a776":
/*!*********************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/TabsNav.vue?vue&type=template&id=5802a776 ***!
  \*********************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nconst _hoisted_1 = {\n  class: \"nav nav-pills mb-4 border-bottom\"\n};\nconst _hoisted_2 = {\n  class: \"nav-item\"\n};\nconst _hoisted_3 = {\n  class: \"nav-item\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"ul\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"li\", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n    class: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)([\"nav-link\", {\n      active: $setup.isCapture\n    }]),\n    onClick: _cache[0] || (_cache[0] = $event => $setup.clickTab('capture'))\n  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"MString\"], {\n    name: \"assessmentgradecapture\"\n  })], 2\n  /* CLASS */\n  )]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"li\", _hoisted_3, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"a\", {\n    class: (0,vue__WEBPACK_IMPORTED_MODULE_0__.normalizeClass)([\"nav-link\", {\n      active: !$setup.isCapture\n    }]),\n    onClick: _cache[1] || (_cache[1] = $event => $setup.clickTab('aggregate'))\n  }, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"MString\"], {\n    name: \"coursegradeaggregation\"\n  })], 2\n  /* CLASS */\n  )])]);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/TabsNav.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/CaptureAggregation.vue?vue&type=template&id=b883dd14":
/*!***************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/CaptureAggregation.vue?vue&type=template&id=b883dd14 ***!
  \***************************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nconst _hoisted_1 = {\n  class: \"row\"\n};\nconst _hoisted_2 = {\n  class: \"col\"\n};\nconst _hoisted_3 = {\n  key: 0\n};\nconst _hoisted_4 = {\n  class: \"col\"\n};\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_router_link = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"router-link\");\n\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"h1\", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"MString\"], {\n    name: \"captureaggregation\"\n  })]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"TabsNav\"], {\n    onTabchange: $setup.tabChange\n  }), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_1, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_2, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"LevelOneSelect\"], {\n    onLevelchange: $setup.levelOneChange\n  }), $setup.currenttab == 'capture' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", _hoisted_3, [$setup.showactivityselect ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)($setup[\"ActivitySelect\"], {\n    key: 0,\n    categoryid: $setup.level1category,\n    onActivityselected: $setup.activity_selected\n  }, null, 8\n  /* PROPS */\n  , [\"categoryid\"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true)])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true)]), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"div\", _hoisted_4, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_router_link, {\n    to: \"/settings\",\n    class: \"btn btn-primary\"\n  }, {\n    default: (0,vue__WEBPACK_IMPORTED_MODULE_0__.withCtx)(() => [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)($setup[\"MString\"], {\n      name: \"settings\"\n    })]),\n    _: 1\n    /* STABLE */\n\n  })])]), $setup.itemid && $setup.currenttab == 'capture' ? ((0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createBlock)($setup[\"CaptureTable\"], {\n    key: 0,\n    itemid: $setup.itemid\n  }, null, 8\n  /* PROPS */\n  , [\"itemid\"])) : (0,vue__WEBPACK_IMPORTED_MODULE_0__.createCommentVNode)(\"v-if\", true)]);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/views/CaptureAggregation.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/SettingsPage.vue?vue&type=template&id=3b4cc18c":
/*!*********************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/SettingsPage.vue?vue&type=template&id=3b4cc18c ***!
  \*********************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\n\nconst _hoisted_1 = /*#__PURE__*/(0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementVNode)(\"h1\", null, \"Settings\", -1\n/* HOISTED */\n);\n\nconst _hoisted_2 = [_hoisted_1];\nfunction render(_ctx, _cache) {\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", null, _hoisted_2);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/views/SettingsPage.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./src/js/getstrings.js":
/*!******************************!*\
  !*** ./src/js/getstrings.js ***!
  \******************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"getstrings\": function() { return /* binding */ getstrings; }\n/* harmony export */ });\n/**\n * Loads strings from Moodle WS\n */\nasync function getstrings(list) {\n  const GU = window.GU;\n  const strings = list.map(name => {\n    return {\n      key: name,\n      component: 'local_gugrades'\n    };\n  });\n  const sfetch = GU.getStrings(strings).then(result => {\n    let value = 0;\n    const translated = {};\n    list.forEach((name, i) => {\n      value = result[i];\n      translated[name] = value;\n    });\n    return translated;\n  });\n  return sfetch;\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/js/getstrings.js?");

/***/ }),

/***/ "./src/main.js":
/*!*********************!*\
  !*** ./src/main.js ***!
  \*********************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var core_js_modules_es_error_cause_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.error.cause.js */ \"./node_modules/core-js/modules/es.error.cause.js\");\n/* harmony import */ var core_js_modules_es_error_cause_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_error_cause_js__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n/* harmony import */ var _App_vue__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./App.vue */ \"./src/App.vue\");\n/* harmony import */ var _router__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./router */ \"./src/router/index.js\");\n\n\n\n // This stuff makes sure that the window.GU variable\n// exists.\n// This can take some time as Moodle runs this once the page\n// has loaded\n\nvar timeout = 1000000;\n\nfunction ensureGUIsSet(timeout) {\n  var start = Date.now();\n  return new Promise(waitForGU);\n\n  function waitForGU(resolve, reject) {\n    if (window.GU) {\n      resolve(window.GU);\n    } else if (timeout && Date.now() - start >= timeout) {\n      reject(new Error(\"timeout\"));\n    } else {\n      setTimeout(waitForGU.bind(this, resolve, reject), 30);\n    }\n  }\n}\n\nensureGUIsSet(timeout).then(() => {\n  (0,vue__WEBPACK_IMPORTED_MODULE_1__.createApp)(_App_vue__WEBPACK_IMPORTED_MODULE_2__[\"default\"]).use(_router__WEBPACK_IMPORTED_MODULE_3__[\"default\"]).mount('#app');\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/main.js?");

/***/ }),

/***/ "./src/router/index.js":
/*!*****************************!*\
  !*** ./src/router/index.js ***!
  \*****************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var vue_router__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vue-router */ \"./node_modules/vue-router/dist/vue-router.mjs\");\n/* harmony import */ var _views_CaptureAggregation_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../views/CaptureAggregation.vue */ \"./src/views/CaptureAggregation.vue\");\n/* harmony import */ var _views_SettingsPage_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/views/SettingsPage.vue */ \"./src/views/SettingsPage.vue\");\n\n\n\nconst routes = [{\n  path: '/',\n  name: 'captureaggregation',\n  component: _views_CaptureAggregation_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]\n}, {\n  path: '/settings',\n  name: 'settings',\n  component: _views_SettingsPage_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]\n}, {\n  path: '/about',\n  name: 'about',\n  // route level code-splitting\n  // this generates a separate chunk (about.[hash].js) for this route\n  // which is lazy-loaded when the route is visited.\n  component: () => __webpack_require__.e(/*! import() | about */ \"about\").then(__webpack_require__.bind(__webpack_require__, /*! ../views/AboutView.vue */ \"./src/views/AboutView.vue\"))\n}];\nconst router = (0,vue_router__WEBPACK_IMPORTED_MODULE_2__.createRouter)({\n  history: (0,vue_router__WEBPACK_IMPORTED_MODULE_2__.createMemoryHistory)(),\n  //history: createWebHistory(process.env.BASE_URL),\n  routes\n});\n/* harmony default export */ __webpack_exports__[\"default\"] = (router);\n\n//# sourceURL=webpack://gugrades_ui/./src/router/index.js?");

/***/ }),

/***/ "./node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css":
/*!**************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css ***!
  \**************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../node_modules/css-loader/dist/runtime/noSourceMaps.js */ \"./node_modules/css-loader/dist/runtime/noSourceMaps.js\");\n/* harmony import */ var _node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../node_modules/css-loader/dist/runtime/api.js */ \"./node_modules/css-loader/dist/runtime/api.js\");\n/* harmony import */ var _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1__);\n// Imports\n\n\nvar ___CSS_LOADER_EXPORT___ = _node_modules_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_1___default()((_node_modules_css_loader_dist_runtime_noSourceMaps_js__WEBPACK_IMPORTED_MODULE_0___default()));\n// Module\n___CSS_LOADER_EXPORT___.push([module.id, \"\\n.cursor-pointer[data-v-09ba6d9b] {\\n        cursor: pointer;\\n}\\n\", \"\"]);\n// Exports\n/* harmony default export */ __webpack_exports__[\"default\"] = (___CSS_LOADER_EXPORT___);\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivitySelect.vue?./node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use%5B1%5D!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./src/App.vue":
/*!*********************!*\
  !*** ./src/App.vue ***!
  \*********************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _App_vue_vue_type_template_id_7ba5bd90__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./App.vue?vue&type=template&id=7ba5bd90 */ \"./src/App.vue?vue&type=template&id=7ba5bd90\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\nconst script = {}\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"])(script, [['render',_App_vue_vue_type_template_id_7ba5bd90__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/App.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/App.vue?");

/***/ }),

/***/ "./src/components/ActivitySelect.vue":
/*!*******************************************!*\
  !*** ./src/components/ActivitySelect.vue ***!
  \*******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ActivitySelect_vue_vue_type_template_id_09ba6d9b_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ActivitySelect.vue?vue&type=template&id=09ba6d9b&scoped=true */ \"./src/components/ActivitySelect.vue?vue&type=template&id=09ba6d9b&scoped=true\");\n/* harmony import */ var _ActivitySelect_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActivitySelect.vue?vue&type=script&setup=true&lang=js */ \"./src/components/ActivitySelect.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _ActivitySelect_vue_vue_type_style_index_0_id_09ba6d9b_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css */ \"./src/components/ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\n\n\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_3__[\"default\"])(_ActivitySelect_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_ActivitySelect_vue_vue_type_template_id_09ba6d9b_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render],['__scopeId',\"data-v-09ba6d9b\"],['__file',\"src/components/ActivitySelect.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivitySelect.vue?");

/***/ }),

/***/ "./src/components/ActivityTree.vue":
/*!*****************************************!*\
  !*** ./src/components/ActivityTree.vue ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _ActivityTree_vue_vue_type_template_id_75d4e0fd__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./ActivityTree.vue?vue&type=template&id=75d4e0fd */ \"./src/components/ActivityTree.vue?vue&type=template&id=75d4e0fd\");\n/* harmony import */ var _ActivityTree_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./ActivityTree.vue?vue&type=script&setup=true&lang=js */ \"./src/components/ActivityTree.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_ActivityTree_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_ActivityTree_vue_vue_type_template_id_75d4e0fd__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/components/ActivityTree.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivityTree.vue?");

/***/ }),

/***/ "./src/components/CaptureTable.vue":
/*!*****************************************!*\
  !*** ./src/components/CaptureTable.vue ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _CaptureTable_vue_vue_type_template_id_90df3f50__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CaptureTable.vue?vue&type=template&id=90df3f50 */ \"./src/components/CaptureTable.vue?vue&type=template&id=90df3f50\");\n/* harmony import */ var _CaptureTable_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CaptureTable.vue?vue&type=script&setup=true&lang=js */ \"./src/components/CaptureTable.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_CaptureTable_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_CaptureTable_vue_vue_type_template_id_90df3f50__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/components/CaptureTable.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/components/CaptureTable.vue?");

/***/ }),

/***/ "./src/components/InitialBar.vue":
/*!***************************************!*\
  !*** ./src/components/InitialBar.vue ***!
  \***************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _InitialBar_vue_vue_type_template_id_116cbe42__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./InitialBar.vue?vue&type=template&id=116cbe42 */ \"./src/components/InitialBar.vue?vue&type=template&id=116cbe42\");\n/* harmony import */ var _InitialBar_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./InitialBar.vue?vue&type=script&setup=true&lang=js */ \"./src/components/InitialBar.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_InitialBar_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_InitialBar_vue_vue_type_template_id_116cbe42__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/components/InitialBar.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/components/InitialBar.vue?");

/***/ }),

/***/ "./src/components/LevelOneSelect.vue":
/*!*******************************************!*\
  !*** ./src/components/LevelOneSelect.vue ***!
  \*******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _LevelOneSelect_vue_vue_type_template_id_c5eed9e4__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./LevelOneSelect.vue?vue&type=template&id=c5eed9e4 */ \"./src/components/LevelOneSelect.vue?vue&type=template&id=c5eed9e4\");\n/* harmony import */ var _LevelOneSelect_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./LevelOneSelect.vue?vue&type=script&setup=true&lang=js */ \"./src/components/LevelOneSelect.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_LevelOneSelect_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_LevelOneSelect_vue_vue_type_template_id_c5eed9e4__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/components/LevelOneSelect.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/components/LevelOneSelect.vue?");

/***/ }),

/***/ "./src/components/MString.vue":
/*!************************************!*\
  !*** ./src/components/MString.vue ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _MString_vue_vue_type_template_id_144c19de__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./MString.vue?vue&type=template&id=144c19de */ \"./src/components/MString.vue?vue&type=template&id=144c19de\");\n/* harmony import */ var _MString_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./MString.vue?vue&type=script&setup=true&lang=js */ \"./src/components/MString.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_MString_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_MString_vue_vue_type_template_id_144c19de__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/components/MString.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/components/MString.vue?");

/***/ }),

/***/ "./src/components/NameFilter.vue":
/*!***************************************!*\
  !*** ./src/components/NameFilter.vue ***!
  \***************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _NameFilter_vue_vue_type_template_id_413b4e93__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./NameFilter.vue?vue&type=template&id=413b4e93 */ \"./src/components/NameFilter.vue?vue&type=template&id=413b4e93\");\n/* harmony import */ var _NameFilter_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./NameFilter.vue?vue&type=script&setup=true&lang=js */ \"./src/components/NameFilter.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_NameFilter_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_NameFilter_vue_vue_type_template_id_413b4e93__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/components/NameFilter.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/components/NameFilter.vue?");

/***/ }),

/***/ "./src/components/PagingBar.vue":
/*!**************************************!*\
  !*** ./src/components/PagingBar.vue ***!
  \**************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _PagingBar_vue_vue_type_template_id_02515d67__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./PagingBar.vue?vue&type=template&id=02515d67 */ \"./src/components/PagingBar.vue?vue&type=template&id=02515d67\");\n/* harmony import */ var _PagingBar_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./PagingBar.vue?vue&type=script&setup=true&lang=js */ \"./src/components/PagingBar.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_PagingBar_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_PagingBar_vue_vue_type_template_id_02515d67__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/components/PagingBar.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/components/PagingBar.vue?");

/***/ }),

/***/ "./src/components/TabsNav.vue":
/*!************************************!*\
  !*** ./src/components/TabsNav.vue ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _TabsNav_vue_vue_type_template_id_5802a776__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./TabsNav.vue?vue&type=template&id=5802a776 */ \"./src/components/TabsNav.vue?vue&type=template&id=5802a776\");\n/* harmony import */ var _TabsNav_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./TabsNav.vue?vue&type=script&setup=true&lang=js */ \"./src/components/TabsNav.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_TabsNav_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_TabsNav_vue_vue_type_template_id_5802a776__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/components/TabsNav.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/components/TabsNav.vue?");

/***/ }),

/***/ "./src/views/CaptureAggregation.vue":
/*!******************************************!*\
  !*** ./src/views/CaptureAggregation.vue ***!
  \******************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _CaptureAggregation_vue_vue_type_template_id_b883dd14__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./CaptureAggregation.vue?vue&type=template&id=b883dd14 */ \"./src/views/CaptureAggregation.vue?vue&type=template&id=b883dd14\");\n/* harmony import */ var _CaptureAggregation_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./CaptureAggregation.vue?vue&type=script&setup=true&lang=js */ \"./src/views/CaptureAggregation.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_CaptureAggregation_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_CaptureAggregation_vue_vue_type_template_id_b883dd14__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/views/CaptureAggregation.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/views/CaptureAggregation.vue?");

/***/ }),

/***/ "./src/views/SettingsPage.vue":
/*!************************************!*\
  !*** ./src/views/SettingsPage.vue ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _SettingsPage_vue_vue_type_template_id_3b4cc18c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./SettingsPage.vue?vue&type=template&id=3b4cc18c */ \"./src/views/SettingsPage.vue?vue&type=template&id=3b4cc18c\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\nconst script = {}\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"])(script, [['render',_SettingsPage_vue_vue_type_template_id_3b4cc18c__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/views/SettingsPage.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/views/SettingsPage.vue?");

/***/ }),

/***/ "./src/components/ActivitySelect.vue?vue&type=script&setup=true&lang=js":
/*!******************************************************************************!*\
  !*** ./src/components/ActivitySelect.vue?vue&type=script&setup=true&lang=js ***!
  \******************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivitySelect_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivitySelect_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActivitySelect.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivitySelect.vue?");

/***/ }),

/***/ "./src/components/ActivityTree.vue?vue&type=script&setup=true&lang=js":
/*!****************************************************************************!*\
  !*** ./src/components/ActivityTree.vue?vue&type=script&setup=true&lang=js ***!
  \****************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityTree_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityTree_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActivityTree.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivityTree.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivityTree.vue?");

/***/ }),

/***/ "./src/components/CaptureTable.vue?vue&type=script&setup=true&lang=js":
/*!****************************************************************************!*\
  !*** ./src/components/CaptureTable.vue?vue&type=script&setup=true&lang=js ***!
  \****************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CaptureTable_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CaptureTable_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./CaptureTable.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/CaptureTable.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/components/CaptureTable.vue?");

/***/ }),

/***/ "./src/components/InitialBar.vue?vue&type=script&setup=true&lang=js":
/*!**************************************************************************!*\
  !*** ./src/components/InitialBar.vue?vue&type=script&setup=true&lang=js ***!
  \**************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_InitialBar_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_InitialBar_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./InitialBar.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/InitialBar.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/components/InitialBar.vue?");

/***/ }),

/***/ "./src/components/LevelOneSelect.vue?vue&type=script&setup=true&lang=js":
/*!******************************************************************************!*\
  !*** ./src/components/LevelOneSelect.vue?vue&type=script&setup=true&lang=js ***!
  \******************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LevelOneSelect_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LevelOneSelect_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./LevelOneSelect.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/LevelOneSelect.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/components/LevelOneSelect.vue?");

/***/ }),

/***/ "./src/components/MString.vue?vue&type=script&setup=true&lang=js":
/*!***********************************************************************!*\
  !*** ./src/components/MString.vue?vue&type=script&setup=true&lang=js ***!
  \***********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MString_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MString_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MString.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/MString.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/components/MString.vue?");

/***/ }),

/***/ "./src/components/NameFilter.vue?vue&type=script&setup=true&lang=js":
/*!**************************************************************************!*\
  !*** ./src/components/NameFilter.vue?vue&type=script&setup=true&lang=js ***!
  \**************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NameFilter_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NameFilter_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./NameFilter.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/NameFilter.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/components/NameFilter.vue?");

/***/ }),

/***/ "./src/components/PagingBar.vue?vue&type=script&setup=true&lang=js":
/*!*************************************************************************!*\
  !*** ./src/components/PagingBar.vue?vue&type=script&setup=true&lang=js ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_PagingBar_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_PagingBar_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./PagingBar.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/PagingBar.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/components/PagingBar.vue?");

/***/ }),

/***/ "./src/components/TabsNav.vue?vue&type=script&setup=true&lang=js":
/*!***********************************************************************!*\
  !*** ./src/components/TabsNav.vue?vue&type=script&setup=true&lang=js ***!
  \***********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TabsNav_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TabsNav_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./TabsNav.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/TabsNav.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/components/TabsNav.vue?");

/***/ }),

/***/ "./src/views/CaptureAggregation.vue?vue&type=script&setup=true&lang=js":
/*!*****************************************************************************!*\
  !*** ./src/views/CaptureAggregation.vue?vue&type=script&setup=true&lang=js ***!
  \*****************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CaptureAggregation_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CaptureAggregation_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./CaptureAggregation.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/CaptureAggregation.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/views/CaptureAggregation.vue?");

/***/ }),

/***/ "./src/App.vue?vue&type=template&id=7ba5bd90":
/*!***************************************************!*\
  !*** ./src/App.vue?vue&type=template&id=7ba5bd90 ***!
  \***************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_App_vue_vue_type_template_id_7ba5bd90__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_App_vue_vue_type_template_id_7ba5bd90__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./App.vue?vue&type=template&id=7ba5bd90 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/App.vue?vue&type=template&id=7ba5bd90\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/App.vue?");

/***/ }),

/***/ "./src/components/ActivitySelect.vue?vue&type=template&id=09ba6d9b&scoped=true":
/*!*************************************************************************************!*\
  !*** ./src/components/ActivitySelect.vue?vue&type=template&id=09ba6d9b&scoped=true ***!
  \*************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivitySelect_vue_vue_type_template_id_09ba6d9b_scoped_true__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivitySelect_vue_vue_type_template_id_09ba6d9b_scoped_true__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActivitySelect.vue?vue&type=template&id=09ba6d9b&scoped=true */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=template&id=09ba6d9b&scoped=true\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivitySelect.vue?");

/***/ }),

/***/ "./src/components/ActivityTree.vue?vue&type=template&id=75d4e0fd":
/*!***********************************************************************!*\
  !*** ./src/components/ActivityTree.vue?vue&type=template&id=75d4e0fd ***!
  \***********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityTree_vue_vue_type_template_id_75d4e0fd__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivityTree_vue_vue_type_template_id_75d4e0fd__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActivityTree.vue?vue&type=template&id=75d4e0fd */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivityTree.vue?vue&type=template&id=75d4e0fd\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivityTree.vue?");

/***/ }),

/***/ "./src/components/CaptureTable.vue?vue&type=template&id=90df3f50":
/*!***********************************************************************!*\
  !*** ./src/components/CaptureTable.vue?vue&type=template&id=90df3f50 ***!
  \***********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CaptureTable_vue_vue_type_template_id_90df3f50__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CaptureTable_vue_vue_type_template_id_90df3f50__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./CaptureTable.vue?vue&type=template&id=90df3f50 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/CaptureTable.vue?vue&type=template&id=90df3f50\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/CaptureTable.vue?");

/***/ }),

/***/ "./src/components/InitialBar.vue?vue&type=template&id=116cbe42":
/*!*********************************************************************!*\
  !*** ./src/components/InitialBar.vue?vue&type=template&id=116cbe42 ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_InitialBar_vue_vue_type_template_id_116cbe42__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_InitialBar_vue_vue_type_template_id_116cbe42__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./InitialBar.vue?vue&type=template&id=116cbe42 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/InitialBar.vue?vue&type=template&id=116cbe42\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/InitialBar.vue?");

/***/ }),

/***/ "./src/components/LevelOneSelect.vue?vue&type=template&id=c5eed9e4":
/*!*************************************************************************!*\
  !*** ./src/components/LevelOneSelect.vue?vue&type=template&id=c5eed9e4 ***!
  \*************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LevelOneSelect_vue_vue_type_template_id_c5eed9e4__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_LevelOneSelect_vue_vue_type_template_id_c5eed9e4__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./LevelOneSelect.vue?vue&type=template&id=c5eed9e4 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/LevelOneSelect.vue?vue&type=template&id=c5eed9e4\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/LevelOneSelect.vue?");

/***/ }),

/***/ "./src/components/MString.vue?vue&type=template&id=144c19de":
/*!******************************************************************!*\
  !*** ./src/components/MString.vue?vue&type=template&id=144c19de ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MString_vue_vue_type_template_id_144c19de__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_MString_vue_vue_type_template_id_144c19de__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./MString.vue?vue&type=template&id=144c19de */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/MString.vue?vue&type=template&id=144c19de\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/MString.vue?");

/***/ }),

/***/ "./src/components/NameFilter.vue?vue&type=template&id=413b4e93":
/*!*********************************************************************!*\
  !*** ./src/components/NameFilter.vue?vue&type=template&id=413b4e93 ***!
  \*********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NameFilter_vue_vue_type_template_id_413b4e93__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_NameFilter_vue_vue_type_template_id_413b4e93__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./NameFilter.vue?vue&type=template&id=413b4e93 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/NameFilter.vue?vue&type=template&id=413b4e93\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/NameFilter.vue?");

/***/ }),

/***/ "./src/components/PagingBar.vue?vue&type=template&id=02515d67":
/*!********************************************************************!*\
  !*** ./src/components/PagingBar.vue?vue&type=template&id=02515d67 ***!
  \********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_PagingBar_vue_vue_type_template_id_02515d67__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_PagingBar_vue_vue_type_template_id_02515d67__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./PagingBar.vue?vue&type=template&id=02515d67 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/PagingBar.vue?vue&type=template&id=02515d67\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/PagingBar.vue?");

/***/ }),

/***/ "./src/components/TabsNav.vue?vue&type=template&id=5802a776":
/*!******************************************************************!*\
  !*** ./src/components/TabsNav.vue?vue&type=template&id=5802a776 ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TabsNav_vue_vue_type_template_id_5802a776__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_TabsNav_vue_vue_type_template_id_5802a776__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./TabsNav.vue?vue&type=template&id=5802a776 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/TabsNav.vue?vue&type=template&id=5802a776\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/TabsNav.vue?");

/***/ }),

/***/ "./src/views/CaptureAggregation.vue?vue&type=template&id=b883dd14":
/*!************************************************************************!*\
  !*** ./src/views/CaptureAggregation.vue?vue&type=template&id=b883dd14 ***!
  \************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CaptureAggregation_vue_vue_type_template_id_b883dd14__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_CaptureAggregation_vue_vue_type_template_id_b883dd14__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./CaptureAggregation.vue?vue&type=template&id=b883dd14 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/CaptureAggregation.vue?vue&type=template&id=b883dd14\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/views/CaptureAggregation.vue?");

/***/ }),

/***/ "./src/views/SettingsPage.vue?vue&type=template&id=3b4cc18c":
/*!******************************************************************!*\
  !*** ./src/views/SettingsPage.vue?vue&type=template&id=3b4cc18c ***!
  \******************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_SettingsPage_vue_vue_type_template_id_3b4cc18c__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_SettingsPage_vue_vue_type_template_id_3b4cc18c__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./SettingsPage.vue?vue&type=template&id=3b4cc18c */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/SettingsPage.vue?vue&type=template&id=3b4cc18c\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/views/SettingsPage.vue?");

/***/ }),

/***/ "./src/components/ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css":
/*!***************************************************************************************************!*\
  !*** ./src/components/ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css ***!
  \***************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _node_modules_vue_style_loader_index_js_clonedRuleSet_12_use_0_node_modules_css_loader_dist_cjs_js_clonedRuleSet_12_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_12_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivitySelect_vue_vue_type_style_index_0_id_09ba6d9b_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/vue-style-loader/index.js??clonedRuleSet-12.use[0]!../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use[1]!../../node_modules/vue-loader/dist/stylePostLoader.js!../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use[2]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css */ \"./node_modules/vue-style-loader/index.js??clonedRuleSet-12.use[0]!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css\");\n/* harmony import */ var _node_modules_vue_style_loader_index_js_clonedRuleSet_12_use_0_node_modules_css_loader_dist_cjs_js_clonedRuleSet_12_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_12_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivitySelect_vue_vue_type_style_index_0_id_09ba6d9b_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_vue_style_loader_index_js_clonedRuleSet_12_use_0_node_modules_css_loader_dist_cjs_js_clonedRuleSet_12_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_12_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivitySelect_vue_vue_type_style_index_0_id_09ba6d9b_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__);\n/* harmony reexport (unknown) */ var __WEBPACK_REEXPORT_OBJECT__ = {};\n/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_vue_style_loader_index_js_clonedRuleSet_12_use_0_node_modules_css_loader_dist_cjs_js_clonedRuleSet_12_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_12_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivitySelect_vue_vue_type_style_index_0_id_09ba6d9b_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== \"default\") __WEBPACK_REEXPORT_OBJECT__[__WEBPACK_IMPORT_KEY__] = function(key) { return _node_modules_vue_style_loader_index_js_clonedRuleSet_12_use_0_node_modules_css_loader_dist_cjs_js_clonedRuleSet_12_use_1_node_modules_vue_loader_dist_stylePostLoader_js_node_modules_postcss_loader_dist_cjs_js_clonedRuleSet_12_use_2_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_ActivitySelect_vue_vue_type_style_index_0_id_09ba6d9b_scoped_true_lang_css__WEBPACK_IMPORTED_MODULE_0__[key]; }.bind(0, __WEBPACK_IMPORT_KEY__)\n/* harmony reexport (unknown) */ __webpack_require__.d(__webpack_exports__, __WEBPACK_REEXPORT_OBJECT__);\n\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivitySelect.vue?");

/***/ }),

/***/ "./node_modules/vue-style-loader/index.js??clonedRuleSet-12.use[0]!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-style-loader/index.js??clonedRuleSet-12.use[0]!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

eval("// style-loader: Adds some css to the DOM by adding a <style> tag\n\n// load the styles\nvar content = __webpack_require__(/*! !!../../node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use[1]!../../node_modules/vue-loader/dist/stylePostLoader.js!../../node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use[2]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css */ \"./node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use[1]!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use[2]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/components/ActivitySelect.vue?vue&type=style&index=0&id=09ba6d9b&scoped=true&lang=css\");\nif(content.__esModule) content = content.default;\nif(typeof content === 'string') content = [[module.id, content, '']];\nif(content.locals) module.exports = content.locals;\n// add the styles to the DOM\nvar add = (__webpack_require__(/*! !../../node_modules/vue-style-loader/lib/addStylesClient.js */ \"./node_modules/vue-style-loader/lib/addStylesClient.js\")[\"default\"])\nvar update = add(\"0d2acdab\", content, false, {\"sourceMap\":false,\"shadowMode\":false});\n// Hot Module Replacement\nif(false) {}\n\n//# sourceURL=webpack://gugrades_ui/./src/components/ActivitySelect.vue?./node_modules/vue-style-loader/index.js??clonedRuleSet-12.use%5B0%5D!./node_modules/css-loader/dist/cjs.js??clonedRuleSet-12.use%5B1%5D!./node_modules/vue-loader/dist/stylePostLoader.js!./node_modules/postcss-loader/dist/cjs.js??clonedRuleSet-12.use%5B2%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			id: moduleId,
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	!function() {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = function(result, chunkIds, fn, priority) {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every(function(key) { return __webpack_require__.O[key](chunkIds[j]); })) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/ensure chunk */
/******/ 	!function() {
/******/ 		__webpack_require__.f = {};
/******/ 		// This file contains only the entry chunk.
/******/ 		// The chunk loading function for additional chunks
/******/ 		__webpack_require__.e = function(chunkId) {
/******/ 			return Promise.all(Object.keys(__webpack_require__.f).reduce(function(promises, key) {
/******/ 				__webpack_require__.f[key](chunkId, promises);
/******/ 				return promises;
/******/ 			}, []));
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/get javascript chunk filename */
/******/ 	!function() {
/******/ 		// This function allow to reference async chunks
/******/ 		__webpack_require__.u = function(chunkId) {
/******/ 			// return url for filenames based on template
/******/ 			return "js/" + chunkId + ".js";
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	!function() {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/load script */
/******/ 	!function() {
/******/ 		var inProgress = {};
/******/ 		var dataWebpackPrefix = "gugrades_ui:";
/******/ 		// loadScript function to load a script via script tag
/******/ 		__webpack_require__.l = function(url, done, key, chunkId) {
/******/ 			if(inProgress[url]) { inProgress[url].push(done); return; }
/******/ 			var script, needAttach;
/******/ 			if(key !== undefined) {
/******/ 				var scripts = document.getElementsByTagName("script");
/******/ 				for(var i = 0; i < scripts.length; i++) {
/******/ 					var s = scripts[i];
/******/ 					if(s.getAttribute("src") == url || s.getAttribute("data-webpack") == dataWebpackPrefix + key) { script = s; break; }
/******/ 				}
/******/ 			}
/******/ 			if(!script) {
/******/ 				needAttach = true;
/******/ 				script = document.createElement('script');
/******/ 		
/******/ 				script.charset = 'utf-8';
/******/ 				script.timeout = 120;
/******/ 				if (__webpack_require__.nc) {
/******/ 					script.setAttribute("nonce", __webpack_require__.nc);
/******/ 				}
/******/ 				script.setAttribute("data-webpack", dataWebpackPrefix + key);
/******/ 				script.src = url;
/******/ 			}
/******/ 			inProgress[url] = [done];
/******/ 			var onScriptComplete = function(prev, event) {
/******/ 				// avoid mem leaks in IE.
/******/ 				script.onerror = script.onload = null;
/******/ 				clearTimeout(timeout);
/******/ 				var doneFns = inProgress[url];
/******/ 				delete inProgress[url];
/******/ 				script.parentNode && script.parentNode.removeChild(script);
/******/ 				doneFns && doneFns.forEach(function(fn) { return fn(event); });
/******/ 				if(prev) return prev(event);
/******/ 			}
/******/ 			;
/******/ 			var timeout = setTimeout(onScriptComplete.bind(null, undefined, { type: 'timeout', target: script }), 120000);
/******/ 			script.onerror = onScriptComplete.bind(null, script.onerror);
/******/ 			script.onload = onScriptComplete.bind(null, script.onload);
/******/ 			needAttach && document.head.appendChild(script);
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/publicPath */
/******/ 	!function() {
/******/ 		__webpack_require__.p = "/local/gugrades/ui/dist/";
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"app": 0
/******/ 		};
/******/ 		
/******/ 		__webpack_require__.f.j = function(chunkId, promises) {
/******/ 				// JSONP chunk loading for javascript
/******/ 				var installedChunkData = __webpack_require__.o(installedChunks, chunkId) ? installedChunks[chunkId] : undefined;
/******/ 				if(installedChunkData !== 0) { // 0 means "already installed".
/******/ 		
/******/ 					// a Promise means "currently loading".
/******/ 					if(installedChunkData) {
/******/ 						promises.push(installedChunkData[2]);
/******/ 					} else {
/******/ 						if(true) { // all chunks have JS
/******/ 							// setup Promise in chunk cache
/******/ 							var promise = new Promise(function(resolve, reject) { installedChunkData = installedChunks[chunkId] = [resolve, reject]; });
/******/ 							promises.push(installedChunkData[2] = promise);
/******/ 		
/******/ 							// start chunk loading
/******/ 							var url = __webpack_require__.p + __webpack_require__.u(chunkId);
/******/ 							// create error before stack unwound to get useful stacktrace later
/******/ 							var error = new Error();
/******/ 							var loadingEnded = function(event) {
/******/ 								if(__webpack_require__.o(installedChunks, chunkId)) {
/******/ 									installedChunkData = installedChunks[chunkId];
/******/ 									if(installedChunkData !== 0) installedChunks[chunkId] = undefined;
/******/ 									if(installedChunkData) {
/******/ 										var errorType = event && (event.type === 'load' ? 'missing' : event.type);
/******/ 										var realSrc = event && event.target && event.target.src;
/******/ 										error.message = 'Loading chunk ' + chunkId + ' failed.\n(' + errorType + ': ' + realSrc + ')';
/******/ 										error.name = 'ChunkLoadError';
/******/ 										error.type = errorType;
/******/ 										error.request = realSrc;
/******/ 										installedChunkData[1](error);
/******/ 									}
/******/ 								}
/******/ 							};
/******/ 							__webpack_require__.l(url, loadingEnded, "chunk-" + chunkId, chunkId);
/******/ 						} else installedChunks[chunkId] = 0;
/******/ 					}
/******/ 				}
/******/ 		};
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = function(chunkId) { return installedChunks[chunkId] === 0; };
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = function(parentChunkLoadingFunction, data) {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some(function(id) { return installedChunks[id] !== 0; })) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkgugrades_ui"] = self["webpackChunkgugrades_ui"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["chunk-vendors"], function() { return __webpack_require__("./src/main.js"); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;