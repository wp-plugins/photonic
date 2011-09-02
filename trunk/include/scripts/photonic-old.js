;(function($) {
    $.fn.waitForImages = function(finishedCallback, eachCallback) {

        eachCallback = eachCallback || function() {};

        if ( ! $.isFunction(finishedCallback) || ! $.isFunction(eachCallback)) {
            throw {
                name: 'invalid_callback',
                message: 'An invalid callback was supplied.'
            };
        }

        var objs = $(this),
            allImgs = objs.find('img'),
            allImgsLength = allImgs.length,
            allImgsLoaded = 0;

        if (allImgsLength == 0) {
            finishedCallback.call(this);
        }

        return objs.each(function() {
            var obj = $(this),
                imgs = obj.find('img');

            if (imgs.length == 0) {
                return true;
            }

            imgs.each(function() {
                var image = new Image,
                    imgElement = this;

                image.onload = function() {
                    allImgsLoaded++;
                    eachCallback.call(imgElement, allImgsLoaded, allImgsLength);
                    if (allImgsLoaded == allImgsLength) {
                        finishedCallback.call(obj[0]);
                        return false;
                    }
                }

                image.src = this.src;
            });
        });
    };
})(jQuery);

/**
 * jQuery Dimensions
 *
 * Copyright (c) 2007 Paul Bakaus (paul.bakaus@googlemail.com) and Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * $LastChangedDate: 2007-06-22 04:38:37 +0200 (Fr, 22 Jun 2007) $
 * $Rev: 2141 $
 *
 * Version: 1.0b2
 */
(function(a){var b=a.fn.height,c=a.fn.width;a.fn.extend({height:function(){if(this[0]==window)return self.innerHeight||a.boxModel&&document.documentElement.clientHeight||document.body.clientHeight;if(this[0]==document)return Math.max(document.body.scrollHeight,document.body.offsetHeight);return b.apply(this,arguments)},width:function(){if(this[0]==window)return self.innerWidth||a.boxModel&&document.documentElement.clientWidth||document.body.clientWidth;if(this[0]==document)return Math.max(document.body.scrollWidth,document.body.offsetWidth);return c.apply(this,arguments)},innerHeight:function(){return this[0]==window||this[0]==document?this.height():this.is(":visible")?this[0].offsetHeight-d(this,"borderTopWidth")-d(this,"borderBottomWidth"):this.height()+d(this,"paddingTop")+d(this,"paddingBottom")},innerWidth:function(){return this[0]==window||this[0]==document?this.width():this.is(":visible")?this[0].offsetWidth-d(this,"borderLeftWidth")-d(this,"borderRightWidth"):this.width()+d(this,"paddingLeft")+d(this,"paddingRight")},outerHeight:function(){return this[0]==window||this[0]==document?this.height():this.is(":visible")?this[0].offsetHeight:this.height()+d(this,"borderTopWidth")+d(this,"borderBottomWidth")+d(this,"paddingTop")+d(this,"paddingBottom")},outerWidth:function(){return this[0]==window||this[0]==document?this.width():this.is(":visible")?this[0].offsetWidth:this.width()+d(this,"borderLeftWidth")+d(this,"borderRightWidth")+d(this,"paddingLeft")+d(this,"paddingRight")},scrollLeft:function(b){if(b!=undefined)return this.each(function(){if(this==window||this==document)window.scrollTo(b,a(window).scrollTop());else this.scrollLeft=b});if(this[0]==window||this[0]==document)return self.pageXOffset||a.boxModel&&document.documentElement.scrollLeft||document.body.scrollLeft;return this[0].scrollLeft},scrollTop:function(b){if(b!=undefined)return this.each(function(){if(this==window||this==document)window.scrollTo(a(window).scrollLeft(),b);else this.scrollTop=b});if(this[0]==window||this[0]==document)return self.pageYOffset||a.boxModel&&document.documentElement.scrollTop||document.body.scrollTop;return this[0].scrollTop},position:function(b,c){var f=this[0],g=f.parentNode,h=f.offsetParent,b=a.extend({margin:false,border:false,padding:false,scroll:false},b||{}),i=f.offsetLeft,j=f.offsetTop,k=f.scrollLeft,l=f.scrollTop;if(a.browser.mozilla||a.browser.msie){i+=d(f,"borderLeftWidth");j+=d(f,"borderTopWidth")}if(a.browser.mozilla){do{if(a.browser.mozilla&&g!=f&&a.css(g,"overflow")!="visible"){i+=d(g,"borderLeftWidth");j+=d(g,"borderTopWidth")}if(g==h)break}while((g=g.parentNode)&&(g.tagName.toLowerCase()!="body"||g.tagName.toLowerCase()!="html"))}var m=e(f,b,i,j,k,l);if(c){a.extend(c,m);return this}else{return m}},offset:function(b,c){var f=0,g=0,h=0,i=0,j=this[0],k=this[0],l,m,n=a.css(j,"position"),o=a.browser.mozilla,p=a.browser.msie,q=a.browser.safari,r=a.browser.opera,s=false,t=false,b=a.extend({margin:true,border:false,padding:false,scroll:true,lite:false},b||{});if(b.lite)return this.offsetLite(b,c);if(j.tagName.toLowerCase()=="body"){f=j.offsetLeft;g=j.offsetTop;if(o){f+=d(j,"marginLeft")+d(j,"borderLeftWidth")*2;g+=d(j,"marginTop")+d(j,"borderTopWidth")*2}else if(r){f+=d(j,"marginLeft");g+=d(j,"marginTop")}else if(p&&jQuery.boxModel){f+=d(j,"borderLeftWidth");g+=d(j,"borderTopWidth")}}else{do{m=a.css(k,"position");f+=k.offsetLeft;g+=k.offsetTop;if(o||p){f+=d(k,"borderLeftWidth");g+=d(k,"borderTopWidth");if(o&&m=="absolute")s=true;if(p&&m=="relative")t=true}l=k.offsetParent;if(b.scroll||o){do{if(b.scroll){h+=k.scrollLeft;i+=k.scrollTop}if(o&&k!=j&&a.css(k,"overflow")!="visible"){f+=d(k,"borderLeftWidth");g+=d(k,"borderTopWidth")}k=k.parentNode}while(k!=l)}k=l;if(k.tagName.toLowerCase()=="body"||k.tagName.toLowerCase()=="html"){if((q||p&&a.boxModel)&&n!="absolute"&&n!="fixed"){f+=d(k,"marginLeft");g+=d(k,"marginTop")}if(o&&!s&&n!="fixed"||p&&n=="static"&&!t){f+=d(k,"borderLeftWidth");g+=d(k,"borderTopWidth")}break}}while(k)}var u=e(j,b,f,g,h,i);if(c){a.extend(c,u);return this}else{return u}},offsetLite:function(b,c){var d=0,f=0,g=0,h=0,i=this[0],j,b=a.extend({margin:true,border:false,padding:false,scroll:true},b||{});do{d+=i.offsetLeft;f+=i.offsetTop;j=i.offsetParent;if(b.scroll){do{g+=i.scrollLeft;h+=i.scrollTop;i=i.parentNode}while(i!=j)}i=j}while(i&&i.tagName.toLowerCase()!="body"&&i.tagName.toLowerCase()!="html");var k=e(this[0],b,d,f,g,h);if(c){a.extend(c,k);return this}else{return k}}});var d=function(b,c){return parseInt(a.css(b.jquery?b[0]:b,c))||0};var e=function(b,c,e,f,g,h){if(!c.margin){e-=d(b,"marginLeft");f-=d(b,"marginTop")}if(c.border&&(a.browser.safari||a.browser.opera)){e+=d(b,"borderLeftWidth");f+=d(b,"borderTopWidth")}else if(!c.border&&!(a.browser.safari||a.browser.opera)){e-=d(b,"borderLeftWidth");f-=d(b,"borderTopWidth")}if(c.padding){e+=d(b,"paddingLeft");f+=d(b,"paddingTop")}if(c.scroll){g-=b.scrollLeft;h-=b.scrollTop}return c.scroll?{top:f-h,left:e-g,scrollTop:h,scrollLeft:g}:{top:f,left:e}}})(jQuery)

/*
 * jQuery Tooltip plugin 1.3
 *
 * http://bassistance.de/jquery-plugins/jquery-plugin-tooltip/
 * http://docs.jquery.com/Plugins/Tooltip
 *
 * Copyright (c) 2006 - 2008 JÃ¶rn Zaefferer
 *
 * $Id: jquery.tooltip.js 5741 2008-06-21 15:22:16Z joern.zaefferer $
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
;(function($){var helper={},current,title,tID,IE=$.browser.msie&&/MSIE\s(5\.5|6\.)/.test(navigator.userAgent),track=false;$.tooltip={blocked:false,defaults:{delay:200,fade:false,showURL:true,extraClass:"",top:15,left:15,id:"tooltip"},block:function(){$.tooltip.blocked=!$.tooltip.blocked;}};$.fn.extend({tooltip:function(settings){settings=$.extend({},$.tooltip.defaults,settings);createHelper(settings);return this.each(function(){$.data(this,"tooltip",settings);this.tOpacity=helper.parent.css("opacity");this.tooltipText=this.title;$(this).removeAttr("title");this.alt="";}).mouseover(save).mouseout(hide).click(hide);},fixPNG:IE?function(){return this.each(function(){var image=$(this).css('backgroundImage');if(image.match(/^url\(["']?(.*\.png)["']?\)$/i)){image=RegExp.$1;$(this).css({'backgroundImage':'none','filter':"progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='"+image+"')"}).each(function(){var position=$(this).css('position');if(position!='absolute'&&position!='relative')$(this).css('position','relative');});}});}:function(){return this;},unfixPNG:IE?function(){return this.each(function(){$(this).css({'filter':'',backgroundImage:''});});}:function(){return this;},hideWhenEmpty:function(){return this.each(function(){$(this)[$(this).html()?"show":"hide"]();});},url:function(){return this.attr('href')||this.attr('src');}});function createHelper(settings){if(helper.parent)return;helper.parent=$('<div id="'+settings.id+'"><h3></h3><div class="body"></div><div class="url"></div></div>').appendTo(document.body).hide();if($.fn.bgiframe)helper.parent.bgiframe();helper.title=$('h3',helper.parent);helper.body=$('div.body',helper.parent);helper.url=$('div.url',helper.parent);}function settings(element){return $.data(element,"tooltip");}function handle(event){if(settings(this).delay)tID=setTimeout(show,settings(this).delay);else
show();track=!!settings(this).track;$(document.body).bind('mousemove',update);update(event);}function save(){if($.tooltip.blocked||this==current||(!this.tooltipText&&!settings(this).bodyHandler))return;current=this;title=this.tooltipText;if(settings(this).bodyHandler){helper.title.hide();var bodyContent=settings(this).bodyHandler.call(this);if(bodyContent.nodeType||bodyContent.jquery){helper.body.empty().append(bodyContent)}else{helper.body.html(bodyContent);}helper.body.show();}else if(settings(this).showBody){var parts=title.split(settings(this).showBody);helper.title.html(parts.shift()).show();helper.body.empty();for(var i=0,part;(part=parts[i]);i++){if(i>0)helper.body.append("<br/>");helper.body.append(part);}helper.body.hideWhenEmpty();}else{helper.title.html(title).show();helper.body.hide();}if(settings(this).showURL&&$(this).url())helper.url.html($(this).url().replace('http://','')).show();else
helper.url.hide();helper.parent.addClass(settings(this).extraClass);if(settings(this).fixPNG)helper.parent.fixPNG();handle.apply(this,arguments);}function show(){tID=null;if((!IE||!$.fn.bgiframe)&&settings(current).fade){if(helper.parent.is(":animated"))helper.parent.stop().show().fadeTo(settings(current).fade,current.tOpacity);else
helper.parent.is(':visible')?helper.parent.fadeTo(settings(current).fade,current.tOpacity):helper.parent.fadeIn(settings(current).fade);}else{helper.parent.show();}update();}function update(event){if($.tooltip.blocked)return;if(event&&event.target.tagName=="OPTION"){return;}if(!track&&helper.parent.is(":visible")){$(document.body).unbind('mousemove',update)}if(current==null){$(document.body).unbind('mousemove',update);return;}helper.parent.removeClass("viewport-right").removeClass("viewport-bottom");var left=helper.parent[0].offsetLeft;var top=helper.parent[0].offsetTop;if(event){left=event.pageX+settings(current).left;top=event.pageY+settings(current).top;var right='auto';if(settings(current).positionLeft){right=$(window).width()-left;left='auto';}helper.parent.css({left:left,right:right,top:top});}var v=viewport(),h=helper.parent[0];if(v.x+v.cx<h.offsetLeft+h.offsetWidth){left-=h.offsetWidth+20+settings(current).left;helper.parent.css({left:left+'px'}).addClass("viewport-right");}if(v.y+v.cy<h.offsetTop+h.offsetHeight){top-=h.offsetHeight+20+settings(current).top;helper.parent.css({top:top+'px'}).addClass("viewport-bottom");}}function viewport(){return{x:$(window).scrollLeft(),y:$(window).scrollTop(),cx:$(window).width(),cy:$(window).height()};}function hide(event){if($.tooltip.blocked)return;if(tID)clearTimeout(tID);current=null;var tsettings=settings(this);function complete(){helper.parent.removeClass(tsettings.extraClass).hide().css("opacity","");}if((!IE||!$.fn.bgiframe)&&tsettings.fade){if(helper.parent.is(':animated'))helper.parent.stop().fadeTo(tsettings.fade,0,complete);else
helper.parent.stop().fadeOut(tsettings.fade,complete);}else
complete();if(settings(this).fixPNG)helper.parent.unfixPNG();}})(jQuery);

/**
 * photonic.js - Contains all custom JavaScript functions required by Photonic
 */
$j = jQuery.noConflict();

function photonicHtmlEncode(value){
	return $j('<div/>').text(value).html();
}

function photonicHtmlDecode(value){
	return $j('<div/>').html(value).text();
}

$j(document).ready(function() {
	// JQuery Cycle stops if there is only one image in it. The following snippet fixes the issue.
	$j('#sliderContent, .sliderContent').each(function() {
		if ($j(this).children().length == 1) {
			var single = this.firstChild;
			$j(single).show();
		}
	});

	$j('a.launch-gallery-fancybox').each(function() {
		$j(this).fancybox({
			transitionIn	:	'elastic',
			transitionOut	:	'elastic',
			speedIn		:	600,
			speedOut		:	200,
			overlayShow	:	true,
			overlayColor:	'#000',
			overlayOpacity: 0.8,
			titleShow	: Photonic_JS.fbox_show_title,
			titlePosition	: Photonic_JS.fbox_title_position
		});
	});

	if (Photonic_JS.slideshow_library == 'slimbox2') {
		$j('a.launch-gallery-slimbox2').slimbox({
			overlayOpacity: 0.8
		});
	}

	$j('a.launch-gallery-colorbox').each(function() {
		$j(this).colorbox({
			opacity: 0.8
		});
	});

	var iteration = 1;
/*
	$j('.suf-flickr-children').live('click', function() {
		var panel = this.id;
		panel = panel.substr(20);
		current_panel = panel;

		$j('#suf-flickr-panel-container-' + panel + ' .suf-flickr-set-thumb').each(function() {
			var html = $j(this).html();
			if (html == '') {
				var photoset_id = this.id;
				photoset_id = photoset_id.substr(photoset_id.lastIndexOf('-') + 1);
				var url = 'http://api.flickr.com/services/rest/?format=json&api_key=' + (Photonic_JS.flickr_api_key) + '&method=flickr.photosets.getInfo&photoset_id=' + photoset_id + '&jsoncallback=?';
				$j.getJSON(url, photonicDisplayEachPhotosetThumbnail);
			}
		});

		$j('#suf-flickr-panel-' + panel + ' ul.slideshow-grid-panel').each(function() {
			var screens = this.children.length;
			var has_arrows = $j(this).parent().children().length > 1;
			if (screens > 0 && !has_arrows) {
				var prev = document.createElement('a');
				prev.id = 'suf-flickr-panel-' + current_panel + '-prev';
				prev.href = '#';
				prev.className = 'panel-previous';
				prev.innerHTML = '&nbsp;';

				var next = document.createElement('a');
				next.id = 'suf-flickr-panel-' + current_panel + '-next';
				next.href = '#';
				next.className = 'panel-next';
				next.innerHTML = '&nbsp;';

				$j(this).before(prev).after(next);
			}
		});

		$j('#suf-flickr-panel-' + panel + ' .suf-flickr-collection').each(function() {
			var html = $j(this).html();
			if (html == '') {
				var collection_id = this.id;
				collection_id = collection_id.substr(collection_id.lastIndexOf('-') + 1);
				var position_id = this.id;
				position_id = position_id.substr(22);
				position_id = position_id.substr(position_id.indexOf('-') + 1);
				position_id = position_id.substr(0, position_id.indexOf('-'));
				current_position = position_id;
				var user_id = eval('photonic_flickr_user_' + position_id);
				var url = 'http://api.flickr.com/services/rest/?format=json&api_key=' + (Photonic_JS.flickr_api_key) + '&method=flickr.collections.getTree&user_id=' + user_id + '&collection_id=' + collection_id + '&jsoncallback=?';
				$j.getJSON(url, rgDisplayCollectionIcons);
			}
		});

		var containerId = 'suf-flickr-panel-container-' + panel;
		panel = '#suf-flickr-panel-' + panel;
		$j(panel).modal({
			autoPosition: false,
			dataCss: { width: '' + Photonic_JS.gallery_panel_width + 'px' },
			overlayCss: { background: '#000' },
			opacity: 90,
			close: true,
			escClose: false,
			containerId: containerId,
//			onClose: function(dialog) { $j.modal.close(); $j(panel).css({ display: 'none' }) },
			onClose: modalClose,
			onShow: modalOnShow,
			onOpen: modalOpen
		});
		var viewport = [$j(window).width(), $j(window).height(), $j(document).scrollLeft(), $j(document).scrollTop()];
		var target = {};
		target.top = parseInt(Math.max(viewport[3] - 20, viewport[3] + ((viewport[1] - $j(panel).height() - 40) * 0.5)), 10);
		target.left = parseInt(Math.max(viewport[2] - 20, viewport[2] + ((viewport[0] - $j(panel).width() - 40) * 0.5)), 10);
		$j('#' + containerId).css({top: target.top, left: target.left});
		$j('.slideshow-grid-panel').cycle({fx: 'scrollHorz', timeout: 0, prev: 'a#' + panel + '-prev', next: 'a#' + panel + '-next', sync: false});

		return false;
	});
*/

	$j('.photonic-flickr-set-thumb').live('click', function() {
		photonicDisplaySetPopup(this);
		return false;
	});

/*
	$j('.suf-flickr-set-thumb').each(function(e) {
//		e.preventDefault();
		var thumb = $j(this);
		var thumb_id = this.id;
		var title = this.title;
//		title = photonicHtmlEncode(title);
		thumb.fancybox({
			href: Photonic_JS.ajaxurl,
			ajax : {
				url: Photonic_JS.ajaxurl,
				data: "action=photonic_flickr_display_set&library=fancybox&setPanel=" + thumb_id + "&title=" + title,
				type: "POST",
				success: function(rsp) {
//					alert(rsp);
				}
			}
		});
//		return false;
	});
*/


	$j('a.modalCloseImg').live('click', function() {
		var thisClass = this.className;
		thisClass = thisClass.substr(14);
		$j('#' + thisClass).hide();
	});

	/**
	 * This function is needed by the Collection's HTML generator. The Collection's JSON object doesn't have the owner's id, so we use a separate AJAX call.
	 *
	 * @param rsp
	 */
/*
	function photonicDisplayEachPhotosetThumbnail(rsp) {
		if (rsp.stat != "ok") {
			return;
		}
		var photoset = rsp.photoset;
		var thumb = "http://farm" + photoset.farm + ".static.flickr.com/" + photoset.server + "/" + photoset.primary + "_" + photoset.secret + "_" + Photonic_JS.flickr_thumbnail_size + ".jpg";
		var img = document.createElement('img');
		img.src = thumb;
		img.alt = photoset.title._content;

		$j('#suf-flickr-set-thumb-' + current_panel + '-' + photoset.id).
			attr({
				href: 'http://www.flickr.com/photos/' + photoset.owner + '/sets/' + photoset.id,
				title: photoset.title._content
			}).
			append(img).
			each(function() {
				// Expand the grid height so that all images are shown
				var li_height = $j(this).parent().parent().parent().height();
				var ul = $j(this).parent().parent().parent().parent();

				if (ul.height() < li_height) {
					ul.height(li_height);
					var panel = '#' + ul.parent().parent().attr('id');
					var viewport = [$j(window).width(), $j(window).height(), $j(document).scrollLeft(), $j(document).scrollTop()];
					var target = {};
					target.top = parseInt(Math.max(viewport[3] - 20, viewport[3] + ((viewport[1] - $j(panel).height() - 40) * 0.5)), 10);
					target.left = parseInt(Math.max(viewport[2] - 20, viewport[2] + ((viewport[0] - $j(panel).width() - 40) * 0.5)), 10);
					$j('#photonic-flickr-panel-container-' + current_panel).css({top: target.top, left: target.left});
					$j('.slideshow-grid-panel').cycle({fx: 'scrollHorz', timeout: 0, prev: 'a#' + panel + '-prev', next: 'a#' + panel + '-next', sync: false });
				}




//				$j('#suf-flickr-panel-' + current_panel).modal({
//					autoPosition: false,
//					dataCss: { width: '' + Photonic_JS.gallery_panel_width + 'px' },
//					overlayCss: { background: '#000' },
//					closeClass: 'suf-flickr-panel-' + current_panel,
//					opacity: 90,
//					close: true,
//					escClose: false,
//					containerId: 'suf-flickr-panel-container-' + current_panel,
//					onClose: function(dialog) { $j.modal.close(); $j('#suf-flickr-panel-' + current_panel).css({ display: 'none' }) }
//				});


			});
	}
*/

/*
	function rgDisplayCollectionIcons(rsp) {
		if (rsp.stat != "ok") {
			return;
		}
		var collections = rsp.collections.collection;
		var html = '';
		var current_id = '';
		for (var i=0; i<collections.length; i++) {
			var collection = collections[i];
			current_id = '-' + collection.id.substr(collection.id.indexOf('-') + 1);
			html += photonicGetFlickrCollectionHTML(collection);
		}

		$j("#suf-flickr-panel-content-" + current_panel + ' .suf-flickr-collection-' + current_panel + current_id).remove();
		$j(html).appendTo($j('#suf-flickr-panel-content-' + current_panel + ' > ul')).show();
	}
*/

	/**
	 * Displays all photos in a Flickr Set. Invoked when the Set is being fetched for the first time.
	 *
	 * @param rsp
	 */
	function photonicDisplaySetImages(rsp) {
		if (rsp.stat != "ok") {
			return;
		}
		var photoset = rsp.photoset;
		var photos = photoset.photo;
		var owner = photoset.owner;
		var main_size = Photonic_JS.flickr_main_size == 'none' ? '' : '_' + Photonic_JS.flickr_main_size;

		if (typeof photos != 'undefined' && photos.length > 0) {
			var col_class = '';
			var columns = eval('photonic_flickr_columns_' + current_position);
/*			if (typeof columns != 'undefined' && columns != 'auto') {
				col_class = 'photonic-gallery-' + columns + 'c';
			}
			else*/ if (Photonic_JS.flickr_photos_pop_per_row_constraint == 'padding') {
				col_class = 'photonic-pad-photos';
			}
			else {
				col_class = 'photonic-gallery-' + Photonic_JS.flickr_photos_pop_constrain_by_count + 'c';
			}

			var div = document.createElement('div');
			div.className = 'photonic-flickr-stream photonic-flickr-panel';
			div.id = 'photonic-flickr-panel-' + current_panel;

			var flickr_url = 'http://www.flickr.com/photos/' + owner + '/sets/' + photoset.id;

			if (!(Photonic_JS.flickr_hide_set_pop_thumbnail && Photonic_JS.flickr_hide_set_pop_title && Photonic_JS.flickr_hide_set_pop_photo_count)) {
				var div_header = document.createElement('div');
				div_header.className = 'photonic-flickr-panel-header fix';
				var header_html = '';

				if (!Photonic_JS.flickr_hide_set_pop_thumbnail) {
					var thumbnail = current_thumbnail.attr('src');
					header_html += '<a href="' + flickr_url + '" class="photonic-header-thumb photonic-flickr-set-pop-thumb" title="' + photonicHtmlEncode(current_title) + '"><img src="' + thumbnail + '" alt="' + current_title + '" /></a>';
				}
				if (!(Photonic_JS.flickr_hide_set_pop_title && Photonic_JS.flickr_hide_set_pop_photo_count)) {
					header_html += '<div class="photonic-header-details photonic-set-pop-details">';
					if (typeof current_title != 'undefined') {
						header_html += '<span class="photonic-header-title photonic-set-pop-title"><a href="' + flickr_url + '">' + current_title + '</a></span>';
					}
					header_html += ' <span class="photonic-header-info photonic-set-pop-info">' + Photonic_JS.flickr_photo_count.replace('{#}', photos.length) + '</span> ';
					header_html += "</div>";
				}

				div_header.innerHTML = header_html;
				div.appendChild(div_header);
			}

 			var div_content = document.createElement('div');
			div_content.className = 'photonic-flickr-panel-content fix';
			div_content.id = 'photonic-flickr-panel-content-' + current_panel;

			if (Photonic_JS.slideshow_library != 'none') {
				var script = document.createElement('script');
				script.type = 'text/javascript';
				if (Photonic_JS.slideshow_library == 'fancybox') {
					script.text = "$j('a.launch-gallery-fancybox').each(function() { $j(this).fancybox({ transitionIn:'elastic', transitionOut:'elastic',speedIn:600,speedOut:200,overlayShow:true,overlayOpacity:0.8,overlayColor:\"#000\",titleShow:Photonic_JS.fbox_show_title,titlePosition:Photonic_JS.fbox_title_position});});";
				}
				else if (Photonic_JS.slideshow_library == 'slimbox2') {
					script.text = "$j('a.launch-gallery-slimbox2').slimbox({ overlayOpacity: 0.8 });";
				}
				else if (Photonic_JS.slideshow_library == 'colorbox') {
					script.text = "$j('a.launch-gallery-colorbox').each(function() { $j(this).colorbox({ opacity: 0.8 });});";
				}
				div_content.appendChild(script);
			}

			var ul = document.createElement('ul');
			ul.className = 'slideshow-grid-panel';

			var rows = 4;
			var li_count = Photonic_JS.gallery_panel_items;
			var li;

			for (var i = 0; i < photos.length; i++) {
				var photo = photos[ i ];
				var thumb = "http://farm" + photo.farm + ".static.flickr.com/" + photo.server + "/" + photo.id + "_" + photo.secret + "_" + Photonic_JS.flickr_thumbnail_size + ".jpg";
				var orig = "http://farm" + photo.farm + ".static.flickr.com/" + photo.server + "/" + photo.id + "_" + photo.secret + main_size + ".jpg";

				if (typeof photo.owner != 'undefined') {
					owner = photo.owner;
				}

				var url = "http://www.flickr.com/photos/" + owner + "/" + photo.id;

				if (i%li_count == 0) {
					li = document.createElement('li');
					li.className = "photonic-flickr-image photonic-flickr-photo ";
				}

				var a = document.createElement('a');
				a.rel = div_content.id;
				if (Photonic_JS.slideshow_library != 'none') {
					a.className = 'launch-gallery-' + Photonic_JS.slideshow_library + " " + Photonic_JS.slideshow_library + " " + col_class;
					a.href = orig;
					a.title = photo.title == '' ? "<a href='" + url + "'>" + Photonic_JS.flickr_view + "</a>" : photo.title + " | <a href='" + url + "'>" + Photonic_JS.flickr_view + "</a>";
				}
				else {
					a.href = url;
					a.title = photo.title;
				}

				var img = document.createElement('img');
				img.alt = photo.title;
				img.src = thumb;

				a.appendChild(img);
				li.appendChild(a);

				if (i%li_count == 0 || i == photos.length - 1) {
					ul.appendChild(li);
				}
			}

			var screens = ul.children.length;
			var prev = document.createElement('a');
			prev.id = 'photonic-flickr-set-' + current_panel + '-prev';
			prev.href = '#';
			prev.className = 'panel-previous';
			prev.innerHTML = '&nbsp;';

			var next = document.createElement('a');
			next.id = 'photonic-flickr-set-' + current_panel + '-next';
			next.href = '#';
			next.className = 'panel-next';
			next.innerHTML = '&nbsp;';

			div_content.appendChild(ul);
			div.appendChild(div_content);

			$j(ul).first('li').waitForImages(function() {
				$j(div).appendTo($j('#photonic-flickr-set-' + current_panel)).show();
				if (screens > 1) {
					$j(this).before(prev)
							.after(next)
							.cycle({
								fx: 'scrollHorz',
								timeout: 0,
								slideResize: false,
								prev: 'a#photonic-flickr-set-' + current_panel + '-prev',
								next: 'a#photonic-flickr-set-' + current_panel + '-next',
								sync: false
							});
				}
				else {
					$j(this).cycle({
						fx: 'scrollHorz',
						timeout: 0,
						slideResize: false,
						sync: false
					});
				}

				$j('#photonic-flickr-panel-' + current_panel).modal({
					autoPosition: false,
					dataCss: { width: '' + Photonic_JS.gallery_panel_width + 'px' },
					overlayCss: { background: '#000' },
					closeClass: 'photonic-flickr-panel-' + current_panel,
					opacity: 90,
					close: true,
					escClose: false,
					containerId: 'photonic-flickr-panel-container-' + current_panel,
					onClose: function(dialog) { $j.modal.close(); $j('#photonic-flickr-panel-' + current_panel).css({ display: 'none' }) },
					onShow: modalOnShow,
					onOpen: modalOpen
				});
				var viewport = [$j(window).width(), $j(window).height(), $j(document).scrollLeft(), $j(document).scrollTop()];
				var target = {};

				target.top = parseInt(Math.max(viewport[3] - 20, viewport[3] + ((viewport[1] - $j('#photonic-flickr-panel-container-' + current_panel).height() - 40) * 0.5)), 10);
				target.left = parseInt(Math.max(viewport[2] - 20, viewport[2] + ((viewport[0] - $j('#photonic-flickr-panel-container-' + current_panel).width() - 40) * 0.5)), 10);

				$j('#photonic-flickr-panel-container-' + current_panel).css({top: target.top, left: target.left });
			});
		}
	}

	// callback function
	function modalOnShow(dialog) {
		var s = this; // refers to the simplemodal object
		$j('.photonic-flickr-set-thumb', dialog.data[0]).click(function () { // use the modal data context
			photonicDisplaySetPopup(this);
			var id = '#' + this.id + '-modal'; // dynamically determine the modal content id based on the link id
			id = '#photonic-flickr-panel-' + this.id.substr(21);

			setTimeout(function () { // wait for 6/10ths of a second, then open the next dialog
				s.close(); // close the current dialog
				$j(id).modal({
					onShow: modalOnShow,
					onOpen: modalOpen,
					onClose: modalClose
				});
			}, 600);

			return false;
		});
	}

	// callback function
	function modalOpen(dialog) {
		dialog.overlay.fadeIn(200, function () {
			dialog.data.hide();
			dialog.container.fadeIn(100, function () {
				dialog.data.fadeIn(100);
				var panel = dialog.data.attr('id');


/*
				var containerId = dialog.container.attr('id');
				var viewport = [$j(window).width(), $j(window).height(), $j(document).scrollLeft(), $j(document).scrollTop()];
				var target = {};
				target.top = parseInt(Math.max(viewport[3] - 20, viewport[3] + ((viewport[1] - $j(panel).height() - 40) * 0.5)), 10);
				target.left = parseInt(Math.max(viewport[2] - 20, viewport[2] + ((viewport[0] - $j(panel).width() - 40) * 0.5)), 10);
				$j('#' + containerId).css({top: target.top, left: target.left});
*/



				$j('.slideshow-grid-panel').cycle({fx: 'scrollHorz', timeout: 0, prev: 'a#' + panel + '-prev', next: 'a#' + panel + '-next'});
			});
		});
	}

	// callback function
	function modalClose(dialog) {
		dialog.data.fadeOut(100, function () {
			dialog.container.fadeOut(100, function () {
				dialog.overlay.fadeOut(200, function () {
					$j.modal.close();
				});
			});
		});
	}

	function photonicDisplaySetPopup(setPanel) {
		var panel = setPanel.id;
		panel = panel.substr(26);
		current_panel = panel;
		var containerId = 'photonic-flickr-panel-container-' + panel;
		panel = '#photonic-flickr-panel-' + panel;
		current_title = setPanel.title;

		current_thumbnail = $j(setPanel).find('img');

		var loading = document.createElement('div');
		loading.className = 'photonic-loading';
		$j(loading).appendTo($j('body')).show();
		if ($j(panel).length == 0) {
			var photoset_id = current_panel.substr(current_panel.lastIndexOf('-') + 1);
			var url = 'http://api.flickr.com/services/rest/?format=json&api_key=' + (Photonic_JS.flickr_api_key) + '&method=flickr.photosets.getPhotos&photoset_id=' + photoset_id + '&jsoncallback=?';
			$j.getJSON(url, photonicDisplaySetImages);
		}
		else {
			$j(panel).modal({
				autoPosition: false,
				dataCss: { width: '' + Photonic_JS.gallery_panel_width + 'px' },
				overlayCss: { background: '#000' },
				opacity: 90,
				close: true,
				escClose: false,
				containerId: containerId,
//				onClose: function(dialog) { $j.modal.close(); $j(panel).css({ display: 'none' }) }
				onClose: modalClose
			});
			var viewport = [$j(window).width(), $j(window).height(), $j(document).scrollLeft(), $j(document).scrollTop()];
			var target = {};
			target.top = parseInt(Math.max(viewport[3] - 20, viewport[3] + ((viewport[1] - $j('#photonic-flickr-panel-' + current_panel).height() - 40) * 0.5)), 10);
			target.left = parseInt(Math.max(viewport[2] - 20, viewport[2] + ((viewport[0] - $j('#photonic-flickr-panel-' + current_panel).width() - 40) * 0.5)), 10);
			$j('#' + containerId).css({top: target.top, left: target.left});
			$j('.slideshow-grid-panel').cycle({fx: 'scrollHorz', timeout: 0, prev: 'a#photonic-flickr-set-' + current_panel + '-prev', next: 'a#photonic-flickr-set-' + current_panel + '-next'});
		}

		return false;
	}
});

function photonicJsonFlickrHeaderApi(rsp) {
	var position = parseInt(Photonic_JS.flickr_position);
	position++;
	Photonic_JS.flickr_position = position;
	if (rsp.stat != "ok") {
		return;
	}

	if (typeof rsp.photoset != 'undefined') {  // Photo sets
		var s = "";
		var photoset = rsp.photoset;
		owner = photoset.owner;
		var id = photoset.id;
		var thumb = "http://farm" + photoset.farm + ".static.flickr.com/" + photoset.server + "/" + photoset.primary + "_" + photoset.secret + "_" + Photonic_JS.flickr_thumbnail_size + ".jpg";
		var cleanTitle = photonicHtmlEncode(photoset.title._content);
		var owner = photoset.owner;

		// Roundabout way to ensure that titles are appropriately escaped.
		var image = "<img src='" + thumb + "' alt='" + cleanTitle + "' />";
		var flickr_link = 'http://www.flickr.com/photos/' + owner + '/sets/' + photoset.id;
		var anchor = "<a href='" + flickr_link + "' class='photonic-header-thumb photonic-flickr-set-solo-thumb' " + cleanTitle + "'>" + image + "</a>";

		if (!(Photonic_JS.flickr_hide_set_thumbnail && Photonic_JS.flickr_hide_set_title && Photonic_JS.flickr_hide_set_photo_count)) {
			// Have to make use of "li" because we are in a "ul"
			s += "<li class='photonic-flickr-set'>";

			if (!Photonic_JS.flickr_hide_set_thumbnail) {
				s += anchor;
			}
			if (!(Photonic_JS.flickr_hide_set_title && Photonic_JS.flickr_hide_set_photo_count)) {
				s += "<div class='photonic-header-details photonic-set-details'>";
				if (!Photonic_JS.flickr_hide_set_title) {
					s += "<div class='photonic-header-title photonic-set-title'><a href='" + flickr_link + "'>" + cleanTitle + '</a></div>';
				}
				if (!Photonic_JS.flickr_hide_set_photo_count) {
					var photo_count = Photonic_JS.flickr_photo_count.replace('{#}', photoset.photos);
					s += "<span class='photonic-header-info photonic-set-photos'>" + photo_count + '</span>';
				}
				s += "</div><!-- .photonic-collection-details -->";
			}
			s += "</li>";
		}
		document.writeln(s);
	}
}

function photonicJsonFlickrStreamApi(rsp) {
	var position = parseInt(Photonic_JS.flickr_position);
	position++;
	Photonic_JS.flickr_position = position;
	var main_size = Photonic_JS.flickr_main_size == 'none' ? '' : '_' + Photonic_JS.flickr_main_size;
	var a_rel = 'photonic-flickr-stream-' + position;

	if (rsp.stat != "ok") {
		return;
	}

	var s = "";

	if (typeof rsp.photos != 'undefined') { // Photos, galleries
		var photos = rsp.photos;
	}
	else if (typeof rsp.photoset != 'undefined') {  // Photo sets
		var photos = rsp.photoset;
		owner = photos.owner;
	}
	else if (typeof rsp.photosets != 'undefined') {   // Multiple Photo Sets
		var photosets = rsp.photosets;
	}
	else if (typeof rsp.collections != 'undefined') {   // Collections
		var collections = rsp.collections;
	}
	else if (typeof rsp.collection != 'undefined') {   // Collections
		var collection = rsp.collection;
	}

	if (typeof photos != 'undefined') {
		var a_class = '';
		if (Photonic_JS.slideshow_library != 'none') {
			a_class = 'launch-gallery-' + Photonic_JS.slideshow_library + " " + Photonic_JS.slideshow_library;
		}
		var col_class = '';
		var columns = eval('photonic_flickr_columns_' + position);
		if (typeof columns != 'undefined' && columns != 'auto') {
			col_class = 'photonic-gallery-' + columns + 'c';
		}

		if (typeof photos.photo != 'undefined') { // When a photoset contains photos
			if (col_class == '' && Photonic_JS.flickr_set_photos_per_row_constraint == 'padding') {
				col_class = 'photonic-pad-photos';
			}
			else if (col_class == '') {
				col_class = 'photonic-gallery-' + Photonic_JS.flickr_set_photos_constrain_by_count + 'c';
			}

			for (var i = 0; i < photos.photo.length; i++) {
				var photo = photos.photo[ i ];
				var thumb = "http://farm" + photo.farm + ".static.flickr.com/" + photo.server + "/" + photo.id + "_" + photo.secret + "_" + Photonic_JS.flickr_thumbnail_size + ".jpg";
				var orig = "http://farm" + photo.farm + ".static.flickr.com/" + photo.server + "/" + photo.id + "_" + photo.secret + main_size + ".jpg";

				if (typeof photo.owner != 'undefined') {
					owner = photo.owner;
				}

				var url = "http://www.flickr.com/photos/" + owner + "/" + photo.id;
				orig = Photonic_JS.slideshow_library == 'none' ? url : orig;

				var origEncodedTitle = photonicHtmlEncode(photo.title);
				var encodedTitle = Photonic_JS.slideshow_library == 'none' ? origEncodedTitle : (origEncodedTitle == '' ? "<a href='" + url + "'>" + Photonic_JS.flickr_view + "</a>" : origEncodedTitle + " | <a href='" + url + "'>" + Photonic_JS.flickr_view + "</a>");
				s += '<li class="photonic-flickr-image photonic-flickr-photo ' + col_class + '"><a href="' + orig + '" class="' + a_class + '" rel="' + a_rel + '" title="' + encodedTitle + '">' + '<img alt="' +
						origEncodedTitle + '"src="' + thumb + '"/>' + '</a></li>';
			}
		}
		else { // When a photoset just contains photoset information, no photos, typically the result of a GetInfo call
			if (col_class == '' && Photonic_JS.flickr_collection_set_per_row_constraint == 'padding') {
				col_class = 'photonic-pad-photosets';
			}
			else if (col_class == '') {
				col_class = 'photonic-gallery-' + Photonic_JS.flickr_collection_set_constrain_by_count + 'c';
			}

			var photoset = photos;
			var id = photoset.id;
			var thumb = "http://farm" + photoset.farm + ".static.flickr.com/" + photoset.server + "/" + photoset.primary + "_" + photoset.secret + "_" + Photonic_JS.flickr_thumbnail_size + ".jpg";
			var cleanTitle = photonicHtmlEncode(photoset.title._content);
			var owner = photoset.owner;

			// Roundabout way to ensure that titles are appropriately escaped.
			var image = "<img src='" + thumb + "' alt='" + cleanTitle + "' />";
			var anchor = "<a href='http://www.flickr.com/photos/" + owner + '/sets/' + photoset.id + "' class='photonic-flickr-set-thumb' " +
					" id='photonic-flickr-set-thumb-" + id + '-' + position + '-' + photoset.id + "' title='" + cleanTitle + "'>" + image + "</a>";

			var text = '';
			if (Photonic_JS.flickr_collection_set_title_display == 'below') {
				text = "<span class='photonic-photoset-title'><a href='http://www.flickr.com/photos/" + owner + '/sets/' + photoset.id + "' title='" + cleanTitle + "'>" + photoset.title._content + "</a></span>";
				if (!Photonic_JS.flickr_hide_collection_set_photos_count_display) {
					text += '<span class="photonic-photoset-photo-count">' + Photonic_JS.flickr_photo_count.replace('{#}', photoset.photos) + '</span>';
				}
			}

			s += "<li class='photonic-flickr-image photonic-flickr-set-thumb " + col_class + "' id='photonic-flickr-set-" + id + '-' + position + "-" + photoset.id + "'>" + anchor + text + "</li>";
		}
	}
	else if (typeof collections != 'undefined') {
		for (var i=0; i < collections.collection.length; i++) {
			var inner_collection = collections.collection[i];
			current_position = position;
			s += photonicGetFlickrCollectionHTML(inner_collection);
		}
	}
	else if (typeof collection != 'undefined') {
		current_position = position;
		s += photonicGetFlickrCollectionHTML(collection);
	}
	else if (typeof photosets != 'undefined') {
		current_position = position;
		s += photonicGetFlickrPhotosetsHTML(photosets);
	}

	document.writeln(s);
}

function photonicGetFlickrCollectionHTML(collection) {
	var position = current_position;
	var id = collection.id.substr(collection.id.indexOf('-') + 1);
	var current_id = id + '-' + position;
	if (typeof current_panel != 'undefined') {
		current_id = current_panel + '-' + id;
	}
	var user_id = eval('photonic_flickr_user_' + current_position);
	var collection_a = "http://www.flickr.com/photos/" + user_id + "/collections/" + id;

	var col_class = '';
	var columns = eval('photonic_flickr_columns_' + position);
	if (typeof columns != 'undefined' && columns != 'auto') {
		col_class = 'photonic-gallery-' + columns + 'c';
	}
	else if (Photonic_JS.flickr_collection_set_per_row_constraint == 'padding') {
		col_class = 'photonic-pad-photosets';
	}
	else {
		col_class = 'photonic-gallery-' + Photonic_JS.flickr_collection_set_constrain_by_count + 'c';
	}

	var s = "<li class='photonic-flickr-image photonic-flickr-collection photonic-flickr-collection-" + current_id + "' id='photonic-flickr-collection-" + current_id+ "'>";
	if (!(Photonic_JS.flickr_hide_collection_thumbnail && Photonic_JS.flickr_hide_collection_title && Photonic_JS.flickr_hide_collection_set_count)) {
//		s += "<div class='photonic-collection'>";
		if (!Photonic_JS.flickr_hide_collection_thumbnail) {
			s += "<a href='" + collection_a + "' class='photonic-header-thumb photonic-flickr-collection-thumb'><img src='" + collection.iconsmall + "' /></a>";
		}
		if (!(Photonic_JS.flickr_hide_collection_title && Photonic_JS.flickr_hide_collection_set_count)) {
			s += "<div class='photonic-header-details photonic-collection-details'>";
			if (!Photonic_JS.flickr_hide_collection_title) {
				s += "<div class='photonic-header-title photonic-collection-title'><a href='" + collection_a + "'>" + collection.title + '</a></div>';
			}
			if (!Photonic_JS.flickr_hide_collection_set_count) {
				var photosets = collection.set;
				if (typeof photosets != 'undefined' && photosets.length > 0) {
					var set_count = Photonic_JS.flickr_set_count.replace('{#}', photosets.length);
					s += "<span class='photonic-header-info photonic-collection-sets'>" + set_count + '</span>';
				}
			}
			s += "</div><!-- .photonic-collection-details -->";
		}
//		s += "</div><!-- .photonic-collection -->";
	}
	s += "</li>";
	return s;
}

function photonicGetFlickrPhotosetsHTML(photosets) {
	var position = current_position;
	var s = '';
	var user_id = eval('photonic_flickr_user_' + position);

	var col_class = '';
	var columns = eval('photonic_flickr_columns_' + position);
	if (typeof columns != 'undefined' && columns != 'auto') {
		col_class = 'photonic-gallery-' + columns + 'c';
	}
	else if (Photonic_JS.flickr_collection_set_per_row_constraint == 'padding') {
		col_class = 'photonic-pad-photosets';
	}
	else {
		col_class = 'photonic-gallery-' + Photonic_JS.flickr_collection_set_constrain_by_count + 'c';
	}

	for (var i=0; i<photosets.photoset.length; i++) {
		var photoset = photosets.photoset[i];
		var id = photoset.id;
		var thumb = "http://farm" + photoset.farm + ".static.flickr.com/" + photoset.server + "/" + photoset.primary + "_" + photoset.secret + "_" + Photonic_JS.flickr_thumbnail_size + ".jpg";
		var cleanTitle = photonicHtmlEncode(photoset.title._content);
		var owner = typeof photoset.owner == 'undefined' ? user_id : photoset.owner;

		// Roundabout way to ensure that titles are appropriately escaped.
		var image = "<img src='" + thumb + "' alt='" + cleanTitle + "' />";
		var anchor = "<a href='http://www.flickr.com/photos/" + owner + '/sets/' + photoset.id + "' class='photonic-flickr-set-thumb' " +
				" id='photonic-flickr-set-thumb-" + id + '-' + position + '-' + photoset.id + "' title='" + cleanTitle + "'>" + image + "</a>";

		var text = '';
		if (Photonic_JS.flickr_collection_set_title_display == 'below') {
			text = "<span class='photonic-photoset-title'><a href='http://www.flickr.com/photos/" + owner + '/sets/' + photoset.id + "' title='" + cleanTitle + "'>" + photoset.title._content + "</a></span>";
			if (!Photonic_JS.flickr_hide_collection_set_photos_count_display) {
				text += '<span class="photonic-photoset-photo-count">' + Photonic_JS.flickr_photo_count.replace('{#}', photoset.photos) + '</span>';
			}
		}

		s += "<li class='photonic-flickr-image photonic-flickr-set-thumb " + col_class + "' id='photonic-flickr-set-" + id + '-' + position + "-" + photoset.id + "'>" + anchor + text + "</li>";
	}
	return s;
}