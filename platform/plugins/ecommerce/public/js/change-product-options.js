(()=>{var t={1873:(t,r,e)=>{var n=e(9325).Symbol;t.exports=n},3729:t=>{t.exports=function(t,r){for(var e=-1,n=null==t?0:t.length;++e<n&&!1!==r(t[e],e,t););return t}},695:(t,r,e)=>{var n=e(8096),o=e(2428),c=e(6449),i=e(3656),a=e(361),u=e(7167),p=Object.prototype.hasOwnProperty;t.exports=function(t,r){var e=c(t),s=!e&&o(t),f=!e&&!s&&i(t),l=!e&&!s&&!f&&u(t),b=e||s||f||l,y=b?n(t.length,String):[],v=y.length;for(var j in t)!r&&!p.call(t,j)||b&&("length"==j||f&&("offset"==j||"parent"==j)||l&&("buffer"==j||"byteLength"==j||"byteOffset"==j)||a(j,v))||y.push(j);return y}},909:(t,r,e)=>{var n=e(641),o=e(8329)(n);t.exports=o},6649:(t,r,e)=>{var n=e(3221)();t.exports=n},641:(t,r,e)=>{var n=e(6649),o=e(5950);t.exports=function(t,r){return t&&n(t,r,o)}},2552:(t,r,e)=>{var n=e(1873),o=e(659),c=e(9350),i=n?n.toStringTag:void 0;t.exports=function(t){return null==t?void 0===t?"[object Undefined]":"[object Null]":i&&i in Object(t)?o(t):c(t)}},7534:(t,r,e)=>{var n=e(2552),o=e(346);t.exports=function(t){return o(t)&&"[object Arguments]"==n(t)}},4901:(t,r,e)=>{var n=e(2552),o=e(294),c=e(346),i={};i["[object Float32Array]"]=i["[object Float64Array]"]=i["[object Int8Array]"]=i["[object Int16Array]"]=i["[object Int32Array]"]=i["[object Uint8Array]"]=i["[object Uint8ClampedArray]"]=i["[object Uint16Array]"]=i["[object Uint32Array]"]=!0,i["[object Arguments]"]=i["[object Array]"]=i["[object ArrayBuffer]"]=i["[object Boolean]"]=i["[object DataView]"]=i["[object Date]"]=i["[object Error]"]=i["[object Function]"]=i["[object Map]"]=i["[object Number]"]=i["[object Object]"]=i["[object RegExp]"]=i["[object Set]"]=i["[object String]"]=i["[object WeakMap]"]=!1,t.exports=function(t){return c(t)&&o(t.length)&&!!i[n(t)]}},8984:(t,r,e)=>{var n=e(5527),o=e(3650),c=Object.prototype.hasOwnProperty;t.exports=function(t){if(!n(t))return o(t);var r=[];for(var e in Object(t))c.call(t,e)&&"constructor"!=e&&r.push(e);return r}},8096:t=>{t.exports=function(t,r){for(var e=-1,n=Array(t);++e<t;)n[e]=r(e);return n}},7301:t=>{t.exports=function(t){return function(r){return t(r)}}},4066:(t,r,e)=>{var n=e(3488);t.exports=function(t){return"function"==typeof t?t:n}},8329:(t,r,e)=>{var n=e(4894);t.exports=function(t,r){return function(e,o){if(null==e)return e;if(!n(e))return t(e,o);for(var c=e.length,i=r?c:-1,a=Object(e);(r?i--:++i<c)&&!1!==o(a[i],i,a););return e}}},3221:t=>{t.exports=function(t){return function(r,e,n){for(var o=-1,c=Object(r),i=n(r),a=i.length;a--;){var u=i[t?a:++o];if(!1===e(c[u],u,c))break}return r}}},4840:(t,r,e)=>{var n="object"==typeof e.g&&e.g&&e.g.Object===Object&&e.g;t.exports=n},659:(t,r,e)=>{var n=e(1873),o=Object.prototype,c=o.hasOwnProperty,i=o.toString,a=n?n.toStringTag:void 0;t.exports=function(t){var r=c.call(t,a),e=t[a];try{t[a]=void 0;var n=!0}catch(t){}var o=i.call(t);return n&&(r?t[a]=e:delete t[a]),o}},361:t=>{var r=/^(?:0|[1-9]\d*)$/;t.exports=function(t,e){var n=typeof t;return!!(e=null==e?9007199254740991:e)&&("number"==n||"symbol"!=n&&r.test(t))&&t>-1&&t%1==0&&t<e}},5527:t=>{var r=Object.prototype;t.exports=function(t){var e=t&&t.constructor;return t===("function"==typeof e&&e.prototype||r)}},3650:(t,r,e)=>{var n=e(4335)(Object.keys,Object);t.exports=n},6009:(t,r,e)=>{t=e.nmd(t);var n=e(4840),o=r&&!r.nodeType&&r,c=o&&t&&!t.nodeType&&t,i=c&&c.exports===o&&n.process,a=function(){try{var t=c&&c.require&&c.require("util").types;return t||i&&i.binding&&i.binding("util")}catch(t){}}();t.exports=a},9350:t=>{var r=Object.prototype.toString;t.exports=function(t){return r.call(t)}},4335:t=>{t.exports=function(t,r){return function(e){return t(r(e))}}},9325:(t,r,e)=>{var n=e(4840),o="object"==typeof self&&self&&self.Object===Object&&self,c=n||o||Function("return this")();t.exports=c},9754:(t,r,e)=>{var n=e(3729),o=e(909),c=e(4066),i=e(6449);t.exports=function(t,r){return(i(t)?n:o)(t,c(r))}},3488:t=>{t.exports=function(t){return t}},2428:(t,r,e)=>{var n=e(7534),o=e(346),c=Object.prototype,i=c.hasOwnProperty,a=c.propertyIsEnumerable,u=n(function(){return arguments}())?n:function(t){return o(t)&&i.call(t,"callee")&&!a.call(t,"callee")};t.exports=u},6449:t=>{var r=Array.isArray;t.exports=r},4894:(t,r,e)=>{var n=e(1882),o=e(294);t.exports=function(t){return null!=t&&o(t.length)&&!n(t)}},3656:(t,r,e)=>{t=e.nmd(t);var n=e(9325),o=e(9935),c=r&&!r.nodeType&&r,i=c&&t&&!t.nodeType&&t,a=i&&i.exports===c?n.Buffer:void 0,u=(a?a.isBuffer:void 0)||o;t.exports=u},1882:(t,r,e)=>{var n=e(2552),o=e(3805);t.exports=function(t){if(!o(t))return!1;var r=n(t);return"[object Function]"==r||"[object GeneratorFunction]"==r||"[object AsyncFunction]"==r||"[object Proxy]"==r}},294:t=>{t.exports=function(t){return"number"==typeof t&&t>-1&&t%1==0&&t<=9007199254740991}},3805:t=>{t.exports=function(t){var r=typeof t;return null!=t&&("object"==r||"function"==r)}},346:t=>{t.exports=function(t){return null!=t&&"object"==typeof t}},7167:(t,r,e)=>{var n=e(4901),o=e(7301),c=e(6009),i=c&&c.isTypedArray,a=i?o(i):n;t.exports=a},5950:(t,r,e)=>{var n=e(695),o=e(8984),c=e(4894);t.exports=function(t){return c(t)?n(t):o(t)}},9935:t=>{t.exports=function(){return!1}}},r={};function e(n){var o=r[n];if(void 0!==o)return o.exports;var c=r[n]={id:n,loaded:!1,exports:{}};return t[n](c,c.exports,e),c.loaded=!0,c.exports}e.n=t=>{var r=t&&t.__esModule?()=>t.default:()=>t;return e.d(r,{a:r}),r},e.d=(t,r)=>{for(var n in r)e.o(r,n)&&!e.o(t,n)&&Object.defineProperty(t,n,{enumerable:!0,get:r[n]})},e.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(t){if("object"==typeof window)return window}}(),e.o=(t,r)=>Object.prototype.hasOwnProperty.call(t,r),e.nmd=t=>(t.paths=[],t.children||(t.children=[]),t),(()=>{"use strict";var t=e(9754),r=e.n(t);function n(t){return n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},n(t)}function o(t,r){for(var e=0;e<r.length;e++){var n=r[e];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,c(n.key),n)}}function c(t){var r=function(t,r){if("object"!=n(t)||!t)return t;var e=t[Symbol.toPrimitive];if(void 0!==e){var o=e.call(t,r||"default");if("object"!=n(o))return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return("string"===r?String:Number)(t)}(t,"string");return"symbol"==n(r)?r:r+""}var i=function(){return t=function t(){!function(t,r){if(!(t instanceof r))throw new TypeError("Cannot call a class as a function")}(this,t),this.priceSale=$(".product-details-content .product-price-sale .js-product-price"),this.priceOriginal=$(".product-details-content .product-price-original .js-product-price");var r=this.priceOriginal;this.priceSale.hasClass("d-none")||(r=this.priceSale),this.basePrice=parseFloat(r.text().replaceAll("$","")),this.priceElement=r,this.extraPrice={},this.eventListeners(),this.formatter=new Intl.NumberFormat("en-US",{style:"currency",currency:"USD"})},(e=[{key:"eventListeners",value:function(){var t=this;$(".product-option .form-radio input").change((function(r){var e=$(r.target).attr("name");t.extraPrice[e]=parseFloat($(r.target).attr("data-extra-price")),t.changeDisplayedPrice()})),$(".product-option .form-checkbox input").change((function(r){var e=$(r.target).attr("name"),n=parseFloat($(r.target).attr("data-extra-price"));void 0===t.extraPrice[e]&&(t.extraPrice[e]=[]),t.extraPrice[e].push(n),t.changeDisplayedPrice()}))}},{key:"changeDisplayedPrice",value:function(){r()(this.extraPrice,(function(t){"number"==typeof t||"object"==n(t)&&t.map((function(t){}))}))}}])&&o(t.prototype,e),c&&o(t,c),Object.defineProperty(t,"prototype",{writable:!1}),t;var t,e,c}();$((function(){new i}))})()})();