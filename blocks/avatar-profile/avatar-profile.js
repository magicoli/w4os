(()=>{var e,t={546:()=>{var e=wp.serverSideRender,t=wp.blocks.registerBlockType,__=wp.i18n.__,l=wp.element.createElement,o=wp.components.TextControl,r=wp.blockEditor.InspectorControls,a=wp.components.PanelBody,n=wp.components.SelectControl,i=wp.components.ToggleControl;t("w4os/avatar-profile",{title:__("OpenSimulator Avatar Profile","w4os"),icon:"admin-users",category:"widgets",supports:{html:!1},attributes:{title:{type:"string",default:""},level:{type:"string",default:"h3"},mini:{type:"boolean",default:!1}},edit:function(t){var s=t.attributes.title,u=t.attributes.level,v=t.attributes.mini,p=t.setAttributes;return l("div",{className:t.className},l(r,null,l(a,{title:__("Block Settings","w4os"),initialOpen:!0},l(o,{label:__("Title","w4os"),value:s,onChange:function(e){p({title:e||void 0})}}),l(n,{label:__("Title Level","w4os"),value:u,options:[{label:"H1",value:"h1"},{label:"H2",value:"h2"},{label:"H3",value:"h3"},{label:"H4",value:"h4"},{label:"H5",value:"h5"},{label:"H6",value:"h6"},{label:"P",value:"p"}],onChange:function(e){p({level:e})}}),l(i,{label:__("Mini Profile","w4os"),checked:v,onChange:function(e){p({mini:e})}}))),l("div",{className:"block-content"},l(e,{block:"w4os/avatar-profile",attributes:t.attributes})))},save:function(){return null}})},841:(e,t,l)=>{"use strict";l(546)}},l={};function o(e){var r=l[e];if(void 0!==r)return r.exports;var a=l[e]={exports:{}};return t[e](a,a.exports,o),a.exports}o.m=t,e=[],o.O=(t,l,r,a)=>{if(!l){var n=1/0;for(v=0;v<e.length;v++){for(var[l,r,a]=e[v],i=!0,s=0;s<l.length;s++)(!1&a||n>=a)&&Object.keys(o.O).every((e=>o.O[e](l[s])))?l.splice(s--,1):(i=!1,a<n&&(n=a));if(i){e.splice(v--,1);var u=r();void 0!==u&&(t=u)}}return t}a=a||0;for(var v=e.length;v>0&&e[v-1][2]>a;v--)e[v]=e[v-1];e[v]=[l,r,a]},o.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return o.d(t,{a:t}),t},o.d=(e,t)=>{for(var l in t)o.o(t,l)&&!o.o(e,l)&&Object.defineProperty(e,l,{enumerable:!0,get:t[l]})},o.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e={54:0,84:0};o.O.j=t=>0===e[t];var t=(t,l)=>{var r,a,[n,i,s]=l,u=0;if(n.some((t=>0!==e[t]))){for(r in i)o.o(i,r)&&(o.m[r]=i[r]);if(s)var v=s(o)}for(t&&t(l);u<n.length;u++)a=n[u],o.o(e,a)&&e[a]&&e[a][0](),e[a]=0;return o.O(v)},l=globalThis.webpackChunkw4os=globalThis.webpackChunkw4os||[];l.forEach(t.bind(null,0)),l.push=t.bind(null,l.push.bind(l))})();var r=o.O(void 0,[84],(()=>o(841)));r=o.O(r)})();