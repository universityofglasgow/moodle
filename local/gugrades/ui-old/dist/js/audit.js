"use strict";
/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
(self["webpackChunkgugrades_ui"] = self["webpackChunkgugrades_ui"] || []).push([["audit"],{

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/AuditPage.vue?vue&type=script&setup=true&lang=js":
/*!*************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/AuditPage.vue?vue&type=script&setup=true&lang=js ***!
  \*************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/runtime-core/dist/runtime-core.esm-bundler.js\");\n/* harmony import */ var _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @vue/runtime-core */ \"./node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js\");\n/* harmony import */ var vue_toastification__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue-toastification */ \"./node_modules/vue-toastification/dist/index.mjs\");\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  __name: 'AuditPage',\n\n  setup(__props, {\n    expose: __expose\n  }) {\n    __expose();\n\n    const mstrings = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.inject)('mstrings');\n    const items = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.ref)([]);\n    const headers = (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.ref)([]);\n    const toast = (0,vue_toastification__WEBPACK_IMPORTED_MODULE_0__.useToast)();\n    (0,_vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.onMounted)(() => {\n      const GU = window.GU;\n      const courseid = GU.courseid;\n      const fetchMany = GU.fetchMany;\n      headers.value = [{\n        text: mstrings.time,\n        value: 'time'\n      }, {\n        text: mstrings.gradeitem,\n        value: 'gradeitem'\n      }, {\n        text: mstrings.by,\n        value: 'username'\n      }, {\n        text: mstrings.relateduser,\n        value: 'relatedusername'\n      }, {\n        text: mstrings.message,\n        value: 'message'\n      }];\n      fetchMany([{\n        methodname: 'local_gugrades_get_audit',\n        args: {\n          courseid: courseid\n        }\n      }])[0].then(result => {\n        items.value = result;\n      }).catch(error => {\n        window.console.error(error);\n        toast.error('Error communicating with server (see console)');\n      });\n    });\n    const __returned__ = {\n      mstrings,\n      items,\n      headers,\n      toast,\n\n      get ref() {\n        return _vue_runtime_core__WEBPACK_IMPORTED_MODULE_2__.ref;\n      },\n\n      get onMounted() {\n        return _vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.onMounted;\n      },\n\n      get inject() {\n        return _vue_runtime_core__WEBPACK_IMPORTED_MODULE_1__.inject;\n      },\n\n      get useToast() {\n        return vue_toastification__WEBPACK_IMPORTED_MODULE_0__.useToast;\n      }\n\n    };\n    Object.defineProperty(__returned__, '__isScriptSetup', {\n      enumerable: false,\n      value: true\n    });\n    return __returned__;\n  }\n\n});\n\n//# sourceURL=webpack://gugrades_ui/./src/views/AuditPage.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/AuditPage.vue?vue&type=template&id=1c6d8180":
/*!******************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/AuditPage.vue?vue&type=template&id=1c6d8180 ***!
  \******************************************************************************************************************************************************************************************************************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* binding */ render; }\n/* harmony export */ });\n/* harmony import */ var vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! vue */ \"./node_modules/vue/dist/vue.runtime.esm-bundler.js\");\n\nfunction render(_ctx, _cache, $props, $setup, $data, $options) {\n  const _component_EasyDataTable = (0,vue__WEBPACK_IMPORTED_MODULE_0__.resolveComponent)(\"EasyDataTable\");\n\n  return (0,vue__WEBPACK_IMPORTED_MODULE_0__.openBlock)(), (0,vue__WEBPACK_IMPORTED_MODULE_0__.createElementBlock)(\"div\", null, [(0,vue__WEBPACK_IMPORTED_MODULE_0__.createVNode)(_component_EasyDataTable, {\n    headers: $setup.headers,\n    items: $setup.items\n  }, null, 8\n  /* PROPS */\n  , [\"headers\", \"items\"])]);\n}\n\n//# sourceURL=webpack://gugrades_ui/./src/views/AuditPage.vue?./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use%5B0%5D!./node_modules/vue-loader/dist/templateLoader.js??ruleSet%5B1%5D.rules%5B3%5D!./node_modules/vue-loader/dist/index.js??ruleSet%5B0%5D.use%5B0%5D");

/***/ }),

/***/ "./src/views/AuditPage.vue":
/*!*********************************!*\
  !*** ./src/views/AuditPage.vue ***!
  \*********************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony import */ var _AuditPage_vue_vue_type_template_id_1c6d8180__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./AuditPage.vue?vue&type=template&id=1c6d8180 */ \"./src/views/AuditPage.vue?vue&type=template&id=1c6d8180\");\n/* harmony import */ var _AuditPage_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AuditPage.vue?vue&type=script&setup=true&lang=js */ \"./src/views/AuditPage.vue?vue&type=script&setup=true&lang=js\");\n/* harmony import */ var _home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./node_modules/vue-loader/dist/exportHelper.js */ \"./node_modules/vue-loader/dist/exportHelper.js\");\n\n\n\n\n;\nconst __exports__ = /*#__PURE__*/(0,_home_howard_Projects_moodle41_app_public_local_gugrades_ui_node_modules_vue_loader_dist_exportHelper_js__WEBPACK_IMPORTED_MODULE_2__[\"default\"])(_AuditPage_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_1__[\"default\"], [['render',_AuditPage_vue_vue_type_template_id_1c6d8180__WEBPACK_IMPORTED_MODULE_0__.render],['__file',\"src/views/AuditPage.vue\"]])\n/* hot reload */\nif (false) {}\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = (__exports__);\n\n//# sourceURL=webpack://gugrades_ui/./src/views/AuditPage.vue?");

/***/ }),

/***/ "./src/views/AuditPage.vue?vue&type=script&setup=true&lang=js":
/*!********************************************************************!*\
  !*** ./src/views/AuditPage.vue?vue&type=script&setup=true&lang=js ***!
  \********************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AuditPage_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__[\"default\"]; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AuditPage_vue_vue_type_script_setup_true_lang_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AuditPage.vue?vue&type=script&setup=true&lang=js */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/AuditPage.vue?vue&type=script&setup=true&lang=js\");\n \n\n//# sourceURL=webpack://gugrades_ui/./src/views/AuditPage.vue?");

/***/ }),

/***/ "./src/views/AuditPage.vue?vue&type=template&id=1c6d8180":
/*!***************************************************************!*\
  !*** ./src/views/AuditPage.vue?vue&type=template&id=1c6d8180 ***!
  \***************************************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": function() { return /* reexport safe */ _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AuditPage_vue_vue_type_template_id_1c6d8180__WEBPACK_IMPORTED_MODULE_0__.render; }\n/* harmony export */ });\n/* harmony import */ var _node_modules_babel_loader_lib_index_js_clonedRuleSet_40_use_0_node_modules_vue_loader_dist_templateLoader_js_ruleSet_1_rules_3_node_modules_vue_loader_dist_index_js_ruleSet_0_use_0_AuditPage_vue_vue_type_template_id_1c6d8180__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!../../node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!../../node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./AuditPage.vue?vue&type=template&id=1c6d8180 */ \"./node_modules/babel-loader/lib/index.js??clonedRuleSet-40.use[0]!./node_modules/vue-loader/dist/templateLoader.js??ruleSet[1].rules[3]!./node_modules/vue-loader/dist/index.js??ruleSet[0].use[0]!./src/views/AuditPage.vue?vue&type=template&id=1c6d8180\");\n\n\n//# sourceURL=webpack://gugrades_ui/./src/views/AuditPage.vue?");

/***/ })

}]);