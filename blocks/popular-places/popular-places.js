(()=>{var t=wp.serverSideRender,e=wp.blocks.registerBlockType,__=wp.i18n.__,l=wp.element.createElement,n=wp.components.TextControl,a=wp.blockEditor.InspectorControls,o=wp.components.PanelBody;e("w4os/popular-places",{title:__("Popular Places","w4os"),icon:"location",category:"widgets",supports:{html:!1},attributes:{title:{type:"string",default:""},max:{type:"number",default:5}},edit:function(e){var s=e.attributes.title,i=e.attributes.max||0,r=e.setAttributes;return l("div",{className:e.className},l(a,null,l(o,{title:__("Block Settings","w4os"),initialOpen:!0},l(n,{label:__("Max Results","w4os"),type:"number",value:i.toString(),onChange:function(t){var e=parseInt(t)<0?0:parseInt(t);r({max:e})}}))),l("div",{className:"block-content"},l(n,{label:__("Title","w4os"),value:s,onChange:function(t){r({title:t||void 0})}}),l(t,{block:"w4os/popular-places",attributes:{title:s,max:i}})))},save:function(){return null}})})();