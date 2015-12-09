"use strict";!function(t){function e(e,n,a){var o="rgb"+(t.support.rgba?"a":"")+"("+parseInt(e[0]+a*(n[0]-e[0]),10)+","+parseInt(e[1]+a*(n[1]-e[1]),10)+","+parseInt(e[2]+a*(n[2]-e[2]),10);return t.support.rgba&&(o+=","+(e&&n?parseFloat(e[3]+a*(n[3]-e[3])):1)),o+")"}function n(t){var e;return(e=/#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/.exec(t))?[parseInt(e[1],16),parseInt(e[2],16),parseInt(e[3],16),1]:(e=/#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])/.exec(t))?[17*parseInt(e[1],16),17*parseInt(e[2],16),17*parseInt(e[3],16),1]:(e=/rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(t))?[parseInt(e[1]),parseInt(e[2]),parseInt(e[3]),1]:(e=/rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9\.]*)\s*\)/.exec(t))?[parseInt(e[1],10),parseInt(e[2],10),parseInt(e[3],10),parseFloat(e[4])]:o[t]}t.extend(!0,t,{support:{rgba:function(){var e=t("script:first"),n=e.css("color"),a=!1;if(/^rgba/.test(n))a=!0;else try{a=n!=e.css("color","rgba(0, 0, 0, 0.5)").css("color"),e.css("color",n)}catch(o){}return a}()}});var a="color backgroundColor borderBottomColor borderLeftColor borderRightColor borderTopColor outlineColor".split(" ");t.each(a,function(a,o){t.Tween.propHooks[o]={get:function(e){return t(e.elem).css(o)},set:function(a){var r=a.elem.style,i=n(t(a.elem).css(o)),s=n(a.end);a.run=function(t){r[o]=e(i,s,t)}}}}),t.Tween.propHooks.borderColor={set:function(o){var r=o.elem.style,i=[],s=a.slice(2,6);t.each(s,function(e,a){i[a]=n(t(o.elem).css(a))});var l=n(o.end);o.run=function(n){t.each(s,function(t,a){r[a]=e(i[a],l,n)})}}};var o={aqua:[0,255,255,1],azure:[240,255,255,1],beige:[245,245,220,1],black:[0,0,0,1],blue:[0,0,255,1],brown:[165,42,42,1],cyan:[0,255,255,1],darkblue:[0,0,139,1],darkcyan:[0,139,139,1],darkgrey:[169,169,169,1],darkgreen:[0,100,0,1],darkkhaki:[189,183,107,1],darkmagenta:[139,0,139,1],darkolivegreen:[85,107,47,1],darkorange:[255,140,0,1],darkorchid:[153,50,204,1],darkred:[139,0,0,1],darksalmon:[233,150,122,1],darkviolet:[148,0,211,1],fuchsia:[255,0,255,1],gold:[255,215,0,1],green:[0,128,0,1],indigo:[75,0,130,1],khaki:[240,230,140,1],lightblue:[173,216,230,1],lightcyan:[224,255,255,1],lightgreen:[144,238,144,1],lightgrey:[211,211,211,1],lightpink:[255,182,193,1],lightyellow:[255,255,224,1],lime:[0,255,0,1],magenta:[255,0,255,1],maroon:[128,0,0,1],navy:[0,0,128,1],olive:[128,128,0,1],orange:[255,165,0,1],pink:[255,192,203,1],purple:[128,0,128,1],violet:[128,0,128,1],red:[255,0,0,1],silver:[192,192,192,1],white:[255,255,255,1],yellow:[255,255,0,1],transparent:[255,255,255,0]}}(jQuery),$(function(){function t(t,e){var n=$(t).clone().html();for(var a in e)n=n.replace("[["+a+"]]",e[a]);return n}function e(e){var a=function(){$(this).parent().parent().remove()};return $("textarea",e).prompt(promptUrl).fastSubmit().autogrow().focus(),e.on("click",".btn-flush",a).submit(function(){var t=e.serialize();return $(":input",e).attr("disabled","disabled"),$.post(e.attr("action"),t,function(t){$(t).hide().insertAfter("nav.text-center").fadeIn(900),$("textarea",e).val("").trigger("keydown"),$(".thumbnails",e).html("")}).always(function(){$(":input",e).removeAttr("disabled")}),!1}).on("click",".btn-cancel",function(){var t=parseInt($(this).data("id"));return $("#entry-"+t).find(".microblog-text").html(o[t]),delete o[t],!1}).delegate("#btn-upload","click",function(){$(".input-file",e).click()}).delegate(".input-file","change",function(){var o=this.files[0];if("image/png"!==o.type&&"image/jpg"!==o.type&&"image/gif"!==o.type&&"image/jpeg"!==o.type)$("#alert").modal("show"),$(".modal-body").text("Format pliku jest nieprawidłowy. Załącznik musi być zdjęciem JPG, PNG lub GIF");else{var r=new FormData(e[0]);$.ajax({url:uploadUrl,type:"POST",data:r,cache:!1,contentType:!1,processData:!1,beforeSend:function(){$(".thumbnails",e).append(t("#tmpl-thumbnail",{src:n,"class":"spinner",fa:"fa fa-spinner fa-spin fa-2x"}))},success:function(t){var n=$(".thumbnail:last",e);$(".spinner",n).remove(),$("img",n).attr("src",t.url),$("<div>",{"class":"btn-flush"}).html('<i class="fa fa-remove fa-2x"></i>').click(a).appendTo(n),$('<input type="hidden" name="thumbnail[]" value="'+t.name+'">').appendTo(n)},error:function(t){$("#alert").modal("show"),"undefined"!=typeof t.responseJSON&&$(".modal-body").text(t.responseJSON.photo[0]),$(".thumbnail:last",e).remove()}},"json")}}),e}var n="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAADIAQMAAAAk14GrAAAABlBMVEXd3d3///+uIkqAAAAAI0lEQVRoge3BMQEAAADCoPVPbQ0PoAAAAAAAAAAAAAAAAHg2MgAAAYXziG4AAAAASUVORK5CYII=";$(document).ajaxError(function(t,e){$("#alert").modal("show");var n;n="undefined"!=typeof e.responseJSON.error?e.responseJSON.error:e.responseJSON.text,$(".modal-body").text(n)});var a,o={},r={},i={click:function(){var t=parseInt($(this).data("count")),e=$(this);return e.addClass("loader").text("Proszę czekać..."),$.post(e.attr("href"),function(n){t=parseInt(n.count),e.data("count",t),e.hasClass("thumbs-on")||e.next(".btn-watch").click(),e.toggleClass("thumbs-on")}).complete(function(){e.removeClass("loader"),e.text(t+" "+declination(t,["głos","głosy","głosów"]))}),!1},enter:function(){var t=parseInt($(this).data("count"));if(t>0){var e=$(this);"undefined"==typeof e.attr("title")&&(a=setTimeout(function(){$.get(e.attr("href"),function(t){if(e.attr("title",t),t.length){var n=t.split("\n").length;e.attr("title",t.replace(/\n/g,"<br />")).data("count",n).text(n+" "+declination(n,["głos","głosy","głosów"])).tooltip({html:!0}).tooltip("show")}})},500))}$(this).off("mouseenter")},leave:function(){clearTimeout(a)}};if($("#microblog").on("click",".btn-reply",function(){$(this).parent().next(".microblog-comments").find("input").focus()}).on("click",".btn-watch",function(){var t=$(this);return $.post(t.attr("href"),function(){t.toggleClass("watch-on")}),!1}).on("click",".btn-thumbs, .btn-sm-thumbs",i.click).on("mouseenter",".btn-thumbs, .btn-sm-thumbs",i.enter).on("mouseleave",".btn-thumbs, .btn-sm-thumbs",i.leave).on("click",".btn-edit",function(t){var n=$(this),a=$("#entry-"+n.data("id")).find(".microblog-text");"undefined"==typeof o[n.data("id")]?$.get(n.attr("href"),function(t){o[n.data("id")]=a.html(),a.html(t);var r=e($(".microblog-submit",a));r.unbind("submit").submit(function(){var t=r.serialize();return $(":input",r).attr("disabled","disabled"),$.post(r.attr("action"),t,function(t){a.html(t),delete o[n.data("id")]}).always(function(){$(":input",r).removeAttr("disabled")}),!1})}):(a.html(o[n.data("id")]),delete o[n.data("id")]),t.preventDefault()}).on("click",".btn-remove",function(){var t=$(this);return $("#confirm").modal("show").one("click",".danger",function(){$.post(t.attr("href"),function(){$("#entry-"+t.data("id")).fadeOut(500)}),$("#confirm").modal("hide")}),!1}).on("focus",".comment-submit input",function(){"undefined"==typeof $(this).data("prompt")&&$(this).prompt(promptUrl).data("prompt","yes")}).on("submit",".comment-submit",function(){var t=$(this),e=$('input[type="text"]',t),n=t.serialize();return e.attr("disabled","disabled"),$.post(t.attr("action"),n,function(n){$(n).hide().insertBefore(t).fadeIn(800),e.val("")}).always(function(){e.removeAttr("disabled")}),!1}).on("click",".btn-sm-edit",function(t){var e=$(this),n=$("#comment-"+e.data("id")).find(".inline-edit"),a=function(){n.html(r[e.data("id")]),delete r[e.data("id")]};"undefined"==typeof r[e.data("id")]?$.get(e.attr("href"),function(t){r[e.data("id")]=n.html(),n.html("");var o=$("<form>"),i=$("<input>",{value:t,"class":"form-control",name:"text",autocomplete:"off"}).keydown(function(t){27===t.keyCode&&a()}).appendTo(o);o.submit(function(){var t=o.serialize();return i.attr("disabled","disabled"),$.post(e.attr("href"),t,function(t){$("#comment-"+e.data("id")).replaceWith(t),delete r[e.data("id")]}).always(function(){i.removeAttr("disabled")}),!1}),o.appendTo(n),i.focus().prompt(promptUrl)}):a(),t.preventDefault()}).on("click",".btn-sm-remove",function(){var t=$(this);return $("#confirm").modal("show").one("click",".danger",function(){$.post(t.attr("href"),function(){$("#comment-"+t.data("id")).fadeOut(500)}),$("#confirm").modal("hide")}),!1}).on("click",".show-all a",function(){var t=$(this);return t.text("Proszę czekać..."),$.get(t.attr("href"),function(e){t.parent().replaceWith(e)}),!1}),e($(".microblog-submit")),"onhashchange"in window){var s=function(){var t=window.location.hash;if("entry"===t.substring(1,6)||"comment"===t.substring(1,8)){var e=$(window.location.hash),n=e.find(".panel");n.length&&(e=n),e.css("background-color","#FFF3CD"),$("#container-fluid").one("mousemove",function(){e.animate({backgroundColor:"#FFF"},1500)})}};window.onhashchange=s,s()}});
//# sourceMappingURL=microblog.js.map