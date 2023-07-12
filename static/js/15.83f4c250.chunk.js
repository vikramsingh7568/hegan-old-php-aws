(this["webpackJsonpreact-hegan-client"]=this["webpackJsonpreact-hegan-client"]||[]).push([[15],{587:function(e,t,a){"use strict";a.r(t);var n=a(9),r=a(20),i=a(23),s=a(7),c=a(4),l=a(41),o=a(2),d=a(25),_=a(15),m=a(549),u=a(538),b=a(533),p=a(534),j=a(522),x=a(494),h=a(535),y=a(523),g=a(518),q=a(517),O=a(528),f=a(513),v=a(519),I=a(594),F=a(539),B=a(540),C=a(541),N=a(542),k=a(510),S=a(440),T=a(441),z=a(37),W=a(34),w=a(14),P=a.n(w),A=a(17),D=a(28),R=a.n(D),E=a(57),M=a(1),G=Object(S.a)(T.a)((function(){return{display:"flex",alignItems:"center"}})),Q=Object(S.a)(G)((function(){return{justifyContent:"center"}})),U=(Object(S.a)(Q)((function(){return{height:"100%",padding:"32px",background:"rgba(0, 0, 0, 0.01)"}})),Object(S.a)("div")((function(e){var t,a=e.theme;return t={margin:"30px"},Object(c.a)(t,a.breakpoints.down("sm"),{margin:"16px"}),Object(c.a)(t,"& .breadcrumb",Object(c.a)({marginBottom:"30px"},a.breakpoints.down("sm"),{marginBottom:"16px"})),t}))),H=Object(S.a)(u.a)((function(){return{minWidth:400,whiteSpace:"pre","& small":{width:50,height:15,borderRadius:500,boxShadow:"0 0 2px 0 rgba(0, 0, 0, 0.12), 0 2px 2px 0 rgba(0, 0, 0, 0.24)"},"& td":{borderBottom:"none"},"& td:first-of-type":{paddingLeft:"16px !important"}}})),J={product_name:"",description:"",category_id:"",brand_name:"",unit_packing:"",expiry_date:"",available_qty:"",hsn_sac_code:"",batch_no:"",mrp:"",product_image:"",min_qty:"",min_qty_discount:"",min_qty_bonus_deal:"",min_qty_trade_rate:"",min_qty_net_rate:"",min_qty_margin_percentage:"",status:"pending",tax_category_id:""},V=A.c().shape({product_name:A.d().min(2,"Product Name must be 10 digit length").required("Product Name is required!"),category_id:A.d().required("Category is required!"),brand_name:A.d().required("Brand is required!"),expiry_date:A.d().required("Expiry Date is required!"),available_qty:A.b().required("Available Quantity is required!"),hsn_sac_code:A.d().required("HSN/SAC is required!"),mrp:A.b().required("MRP is required!"),min_qty:A.b().required("Quantity is required!"),min_qty_discount:A.b().required("Discount (%) is required!"),min_qty_bonus_deal:A.d().required("Bonus (Deal) is required!"),min_qty_trade_rate:A.b().required("Trade Rate is required!"),tax_category_id:A.b().required("Tax Category is required!")});t.default=function(){var e=Object(d.g)().row_id,t=Object(l.c)(),a=(Object(W.a)().register,Object(d.f)()),u=Object(o.useState)(!1),S=Object(s.a)(u,2),w=S[0],A=S[1],D=Object(o.useState)(!1),G=Object(s.a)(D,2),Q=G[0],L=G[1],K=Object(o.useState)(!1),X=Object(s.a)(K,2),Y=X[0],Z=X[1],$=Object(o.useState)(!1),ee=Object(s.a)($,2),te=ee[0],ae=ee[1],ne=Object(o.useState)(J),re=Object(s.a)(ne,2),ie=re[0],se=re[1],ce=Object(o.useState)(J),le=Object(s.a)(ce,2),oe=le[0],de=le[1];Object(o.useEffect)((function(){_e()}),[]);var _e=function(){var t=Object(i.a)(Object(r.a)().mark((function t(){return Object(r.a)().wrap((function(t){for(;;)switch(t.prev=t.next){case 0:return A(!0),t.prev=1,t.next=4,P.a.get("/sanctum/csrf-cookie").then((function(t){P.a.post("/api/client/get-brands").then((function(e){200===e.data.response_code?Z(e.data.data):e.data.response_code,A(!1)})).catch((function(e){A(!1),console.error(e)})),P.a.post("/api/client/get-categories").then((function(e){200===e.data.response_code?L(e.data.data):e.data.response_code,A(!1)})).catch((function(e){A(!1),console.error(e)})),P.a.post("/api/client/get-tax-categories").then((function(e){200===e.data.response_code?ae(e.data.data):e.data.response_code,A(!1)})).catch((function(e){A(!1),console.error(e)})),P.a.post("/api/client/get-medicine",{row_id:e}).then((function(e){200===e.data.response_code?se(e.data.data):e.data.response_code,A(!1)})).catch((function(e){A(!1),console.error(e)}))}));case 4:t.next=9;break;case 6:t.prev=6,t.t0=t.catch(1),A(!1);case 9:case"end":return t.stop()}}),t,null,[[1,6]])})));return function(){return t.apply(this,arguments)}}(),me=function(e,t){var a=0,n=0,r="",i=0,s=0,c=0,l=0,o="";switch(e.target.name){case"min_qty":a=e.target.value?parseInt(e.target.value):0,n=t.min_qty_discount?parseInt(t.min_qty_discount):0,r=t.min_qty_bonus_deal?t.min_qty_bonus_deal:0,i=t.min_qty_trade_rate?parseInt(t.min_qty_trade_rate):0,o=t.tax_category_id?parseInt(t.tax_category_id):0;break;case"min_qty_discount":n=e.target.value?parseInt(e.target.value):0,a=t.min_qty?parseInt(t.min_qty):0,r=t.min_qty_bonus_deal?t.min_qty_bonus_deal:0,i=t.min_qty_trade_rate?parseInt(t.min_qty_trade_rate):0,o=t.tax_category_id?parseInt(t.tax_category_id):0;break;case"min_qty_bonus_deal":r=e.target.value,a=t.min_qty?parseInt(t.min_qty):0,n=t.min_qty_discount?parseInt(t.min_qty_discount):0,i=t.min_qty_trade_rate?parseInt(t.min_qty_trade_rate):0,o=t.tax_category_id?parseInt(t.tax_category_id):0;break;case"min_qty_trade_rate":i=e.target.value?parseInt(e.target.value):0,a=t.min_qty?parseInt(t.min_qty):0,n=t.min_qty_discount?parseInt(t.min_qty_discount):0,r=t.min_qty_bonus_deal,o=t.tax_category_id?parseInt(t.tax_category_id):0;break;case"tax_category_id":o=e.target.value?parseInt(e.target.value):0,a=t.min_qty?parseInt(t.min_qty):0,n=t.min_qty_discount?parseInt(t.min_qty_discount):0,r=t.min_qty_bonus_deal?t.min_qty_bonus_deal:0,i=t.min_qty_trade_rate?parseInt(t.min_qty_trade_rate):0;break;default:a=t.min_qty?parseInt(t.min_qty):0,n=t.min_qty_discount?parseInt(t.min_qty_discount):0,r=t.min_qty_bonus_deal?t.min_qty_bonus_deal:0,i=t.min_qty_trade_rate?parseInt(t.min_qty_trade_rate):0,o=t.tax_category_id?parseInt(t.tax_category_id):0}var d=0,_=0,m="";if(a&&i)if(_=a*i,""==r||0==r||Number.isInteger(r))d=_-_*n/100,console.log("Aamount_without_tax----:"+d);else if(r)if((m=(r=r.replace(/[\(\)]/g,"")).split("+")).length>1){var u=m[0]?parseInt(m[0]):0,b=m[1]?parseInt(m[1]):0,p=_-_*(b/(u+b)*100)/100;d=p-p*n/100}else d=_-_*n/100;var j=0;if(""!==o&&0!==o){var x=te.filter((function(e){return e.id===o}));x&&(c=parseFloat(x[0].igst),l=parseFloat(x[0].cess),j=c&&l?c+l:c)}return"NaN"===(s=d+d*j/100)&&(s=0),s},ue=function(e){de(Object(n.a)(Object(n.a)({},oe),{},Object(c.a)({},e.target.name,e.target.files[0])))};return Object(M.jsxs)(U,{className:"add-medicine",children:[Object(M.jsx)(T.a,{className:"breadcrumb",children:Object(M.jsx)(E.a,{routeSegments:[{name:"dashboard",path:"/dashboard"},{name:"Edit Medicine"}]})}),Object(M.jsxs)(b.a,{className:"card",children:[Object(M.jsx)(p.a,{title:"Edit Medicine",titleTypographyProps:{variant:"h4",fontSize:"16px",fontWeight:"600",textTransform:"uppercase",textAlign:"center"},action:Object(M.jsx)(M.Fragment,{children:Object(M.jsx)(j.a,{title:"Back",component:_.b,to:"/dashboard/medicine/list",color:"inherit",variant:"outlined",size:"small",sx:{m:.5},children:"Back"})})}),Object(M.jsx)(x.a,{}),Object(M.jsx)(h.a,{children:Object(M.jsx)(z.a,{onSubmit:function(t,n){!function(t,n){A(!0);try{P.a.get("/sanctum/csrf-cookie").then((function(r){var i=new FormData;for(var s in t)"product_image"===s?i.append(s,oe[s]):t[s]?i.append(s,t[s]):i.append(s,"");i.append("row_id",e),P.a.post("/api/client/update-medicine-stock",i,{headers:{"Content-Type":"multipart/form-data"}}).then((function(e){200===e.data.response_code?(R()({title:"Good job!",text:e.data.message,icon:"success"}),a("/dashboard/medicine/list")):n.setErrors(e.data.errors),A(!1)})).catch((function(e){console.error(e)}))}))}catch(r){console.log(r),A(!1)}}(t,n)},initialValues:ie,enableReinitialize:!0,validationSchema:V,children:function(e){var a=e.values,r=e.errors,i=e.touched,s=e.handleChange,c=e.handleBlur,l=e.handleSubmit,o=e.setFieldValue;return Object(M.jsxs)("form",{onSubmit:l,children:[Object(M.jsxs)(y.a,{container:!0,style:{padding:"20px 0px"},spacing:2,children:[Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"product_name",className:"form-label",children:"Product Name"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"text",name:"product_name",variant:"outlined",onBlur:c,value:a.product_name,onChange:s,helperText:i.product_name&&r.product_name,error:Boolean(r.product_name&&i.product_name),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"category_id",className:"form-label",children:"Category"}),Object(M.jsx)(O.a,{fullWidth:!0,sx:{mb:3},size:"small",children:Object(M.jsxs)(f.a,{labelId:"status-label",id:"category_id",name:"category_id",onChange:s,variant:"outlined",onBlur:c,value:a.category_id,error:Boolean(r.category_id&&i.category_id),children:[Object(M.jsx)(v.a,{value:"",children:"Select Category"},"sg"),Q&&Q.map((function(e){return Object(M.jsx)(v.a,{value:e.id,children:e.name},e.id)}))]})})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"brand_id",className:"form-label",children:"Brand"}),Object(M.jsx)(O.a,{fullWidth:!0,sx:{mb:3},size:"small",children:Y&&Object(M.jsxs)(M.Fragment,{children:[Object(M.jsx)(I.a,{freeSolo:!0,size:"small",id:"brand_name",onChange:function(e,t){o("brand_name",t)},name:"brand_name",value:a.brand_name,options:Y&&Y.map((function(e){return e.name})),renderInput:function(e){return Object(M.jsx)(q.a,Object(n.a)({onChange:s,onBlur:c,name:"brand_name",variant:"outlined",value:a.brand_name,helperText:i.brand_name&&r.brand_name,error:Boolean(r.brand_name&&i.brand_name)},e))}}),r.brand_name&&i.brand_name&&Object(M.jsx)("div",{style:{color:"#ff3d57"},children:"".concat(r.brand_name)})]})}),Object(M.jsx)("label",{htmlFor:"unit_packing",className:"form-label",children:"Unit Packing"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"text",name:"unit_packing",variant:"outlined",onBlur:c,value:a.unit_packing,onChange:s,helperText:i.unit_packing&&r.unit_packing,error:Boolean(r.unit_packing&&i.unit_packing),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,lg:6,children:[Object(M.jsx)("label",{htmlFor:"description",className:"form-label",children:"Description"}),Object(M.jsx)("br",{}),Object(M.jsx)(q.a,{fullWidth:!0,multiline:!0,minRows:6,size:"small",type:"text",name:"description",variant:"outlined",onBlur:c,value:a.description||"",onChange:s,sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"expiry_date",className:"form-label",children:"Expiry Date"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"date",name:"expiry_date",variant:"outlined",onBlur:c,value:a.expiry_date,onChange:s,helperText:i.expiry_date&&r.expiry_date,error:Boolean(r.expiry_date&&i.expiry_date),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"available_qty",className:"form-label",children:"Available Quantity"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"number",name:"available_qty",variant:"outlined",onBlur:c,value:a.available_qty,onChange:s,helperText:i.available_qty&&r.available_qty,error:Boolean(r.available_qty&&i.available_qty),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"hsn_sac_code",className:"form-label",children:"HSN/SAC Code"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"text",name:"hsn_sac_code",variant:"outlined",onBlur:c,value:a.hsn_sac_code,onChange:s,helperText:i.hsn_sac_code&&r.hsn_sac_code,error:Boolean(r.hsn_sac_code&&i.hsn_sac_code),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,children:[Object(M.jsx)("label",{htmlFor:"batch_no",className:"form-label",children:"Batch No."}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"text",name:"batch_no",variant:"outlined",onBlur:c,value:a.batch_no,onChange:s,helperText:i.batch_no&&r.batch_no,error:Boolean(r.batch_no&&i.batch_no),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"mrp",className:"form-label",children:"MRP"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"number",name:"mrp",variant:"outlined",onBlur:c,value:a.mrp||"",onChange:function(e){s(e);var t=parseFloat(me(e,a)),n=e.target.value?parseInt(e.target.value):0,r=(n-t/(a.min_qty?parseInt(a.min_qty):0))/n*100;o("min_qty_net_rate",t.toFixed(2)),o("min_qty_margin_percentage",r.toFixed(2))},helperText:i.mrp&&r.mrp,error:Boolean(r.mrp&&i.mrp),sx:{mb:3}})]}),Object(M.jsx)(y.a,{item:!0,sm:6,xs:12})]}),Object(M.jsxs)(y.a,{container:!0,style:{padding:"20px 0px"},spacing:2,children:[Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,lg:2,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"min_qty",className:"form-label",children:"Quantity"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"number",name:"min_qty",variant:"outlined",onBlur:c,value:a.min_qty,onChange:function(e){s(e);var t=parseFloat(me(e,a)),n=a.mrp?parseInt(a.mrp):0,r=(n-t/(e.target.value?parseInt(e.target.value):0))/n*100;o("min_qty_net_rate",t.toFixed(2)),o("min_qty_margin_percentage",r.toFixed(2))},helperText:i.min_qty&&r.min_qty,error:Boolean(r.min_qty&&i.min_qty),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,lg:2,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"min_qty_discount",className:"form-label",children:"Discount (%)"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"number",name:"min_qty_discount",variant:"outlined",onBlur:c,value:a.min_qty_discount,onChange:function(e){s(e);var t=parseFloat(me(e,a)),n=a.mrp?parseInt(a.mrp):0,r=(n-t/(a.min_qty?parseInt(a.min_qty):0))/n*100;o("min_qty_net_rate",t.toFixed(2)),o("min_qty_margin_percentage",r.toFixed(2))},helperText:i.min_qty_discount&&r.min_qty_discount,error:Boolean(r.min_qty_discount&&i.min_qty_discount),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,lg:2,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"min_qty_bonus_deal",className:"form-label",children:"Bonus (Deal)"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"text",name:"min_qty_bonus_deal",variant:"outlined",onBlur:c,value:a.min_qty_bonus_deal||"",onChange:function(e){s(e);var t=parseFloat(me(e,a)),n=a.mrp?parseInt(a.mrp):0,r=(n-t/(a.min_qty?parseInt(a.min_qty):0))/n*100;o("min_qty_net_rate",t.toFixed(2)),o("min_qty_margin_percentage",r.toFixed(2))},helperText:i.min_qty_bonus_deal&&r.min_qty_bonus_deal,error:Boolean(r.min_qty_bonus_deal&&i.min_qty_bonus_deal),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,lg:2,children:[Object(M.jsx)(g.a,{required:!0,htmlFor:"min_qty_trade_rate",className:"form-label",children:"Trade Rate"}),Object(M.jsx)(q.a,{fullWidth:!0,size:"small",type:"number",name:"min_qty_trade_rate",variant:"outlined",onBlur:c,value:a.min_qty_trade_rate,onChange:function(e){s(e);var t=parseFloat(me(e,a)),n=a.mrp?parseInt(a.mrp):0,r=(n-t/(a.min_qty?parseInt(a.min_qty):0))/n*100;o("min_qty_net_rate",t.toFixed(2)),o("min_qty_margin_percentage",r.toFixed(2))},helperText:i.min_qty_trade_rate&&r.min_qty_trade_rate,error:Boolean(r.min_qty_trade_rate&&i.min_qty_trade_rate),sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,xs:12,lg:2,children:[Object(M.jsx)("label",{htmlFor:"min_qty_net_rate",className:"form-label",children:"Net Rate"}),Object(M.jsx)(q.a,{fullWidth:!0,disabled:!0,size:"small",type:"text",name:"min_qty_net_rate",variant:"outlined",onBlur:c,value:a.min_qty_net_rate,onChange:s,sx:{mb:3}})]}),Object(M.jsxs)(y.a,{item:!0,sm:6,lg:2,xs:12,children:[Object(M.jsx)("label",{htmlFor:"min_qty_margin_percentage",className:"form-label",children:"Margin%"}),Object(M.jsx)(q.a,{fullWidth:!0,disabled:!0,size:"small",type:"text",name:"min_qty_margin_percentage",variant:"outlined",onBlur:c,value:a.min_qty_margin_percentage,onChange:s,sx:{mb:3}})]})]}),Object(M.jsx)("h4",{style:{color:t.palette.primary.main,fontSize:"18px"},children:"Tax Category"}),Object(M.jsxs)(y.a,{container:!0,style:{padding:"20px 0px"},spacing:2,children:[Object(M.jsx)(y.a,{item:!0,sm:12,xs:12,lg:8,children:Object(M.jsxs)(T.a,{overflow:"auto",children:[Object(M.jsxs)(H,{children:[Object(M.jsx)(F.a,{children:Object(M.jsxs)(B.a,{children:[Object(M.jsx)(C.a,{}),Object(M.jsx)(C.a,{children:"CGST"}),Object(M.jsx)(C.a,{children:"SGST"}),Object(M.jsx)(C.a,{children:"IGST"}),Object(M.jsx)(C.a,{children:"CESS"})]})}),Object(M.jsx)(N.a,{children:te&&te.map((function(e,t){return Object(M.jsxs)(B.a,{children:[Object(M.jsx)(C.a,{children:Object(M.jsx)(k.a,{checked:a.tax_category_id==e.id,onChange:function(e){s(e);var t=parseFloat(me(e,a)),n=a.mrp?parseInt(a.mrp):0,r=(n-t/(a.min_qty?parseInt(a.min_qty):0))/n*100;o("min_qty_net_rate",t.toFixed(2)),o("min_qty_margin_percentage",r.toFixed(2))},value:e.id,name:"tax_category_id",inputProps:{"aria-label":"A"}})}),Object(M.jsx)(C.a,{children:e.cgst}),Object(M.jsx)(C.a,{children:e.sgst}),Object(M.jsx)(C.a,{children:e.igst}),Object(M.jsx)(C.a,{children:e.cess})]},t)}))})]}),r.tax_category_id&&i.tax_category_id&&Object(M.jsx)("div",{style:{color:"#ff3d57"},children:"".concat(r.tax_category_id)})]})}),Object(M.jsxs)(y.a,{item:!0,sm:12,xs:12,lg:4,style:{textAlign:"center"},children:[Object(M.jsx)("label",{htmlFor:"product_image",className:"form-label",children:"Upload Image"}),a.product_image&&Object(M.jsxs)(M.Fragment,{children:[Object(M.jsx)("br",{}),Object(M.jsx)("br",{}),Object(M.jsx)("img",{src:a.product_image,style:{width:"300px",height:"200px"},title:"Product Image"}),Object(M.jsx)("br",{})]}),Object(M.jsx)(O.a,{sx:{mb:3,float:"right"},children:Object(M.jsx)("input",{id:"upload-photo",name:"product_image",type:"file",onChange:ue})}),r.product_image&&i.product_image&&Object(M.jsx)("div",{style:{color:"#ff3d57"},children:"".concat(r.product_image)})]})]}),Object(M.jsx)(y.a,{container:!0,style:{padding:"20px 0px"},spacing:2,children:Object(M.jsx)(y.a,{item:!0,sm:6,xs:12,lg:2,children:Object(M.jsx)(m.a,{fullWidth:!0,type:"submit",color:"primary",loading:w,variant:"contained",sx:{mb:2,mt:3},children:"Update"})})})]})}})})]})]})}}}]);
//# sourceMappingURL=15.83f4c250.chunk.js.map