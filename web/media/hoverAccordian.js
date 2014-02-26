/**
 * HoverAccordion - jQuery plugin for intuitively opening accordions and menus
 * http://berndmatzner.de/jquery/hoveraccordion/
 * Copyright (c) 2008 Bernd Matzner
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * Requires jQuery 1.2.1 or higher
 * Version: 0.5.0
 *
 * Usage:
 * $('#YourUnorderedListID').hoverAccordion();
 * or
 * $('.YourUnorderedListClass').hoverAccordion({
 *  speed: 'slow',
 *  activateitem: '1',
 *  active: 'current',
 *  header: 'title',
 *  hover: 'highlight',
 *  opened: 'arrowdown',
 *  closed: 'arrowup'
 *  keepheight: 'true'
 * });
 */
(function($){
    $.fn.hoverAccordion = function(options){
        // Setup options
        options = jQuery.extend({
            speed: 'fast', // Speed at which the subitems open up - valid options are: slow, normal, fast
            activateitem: 'true', // 'true': Automatically activate items with links corresponding to the current page, '2': Activate item #2
            active: 'active', // Class name of the initially active element
            header: 'header', // Class name for header items
            hover: 'hover', // Class name for hover effect
            opened: 'opened', // Class name for open header items
            closed: 'closed', // Class name for closed header items
            keepheight: 'true' // 'true': Set the height of each accordion item to the size of the largest one, 'false': Leave height as is
        }, options);
        
        // Current hover status
        var thislist = this;
        
        // Current URL
        var thisurl = window.location.href;
        
        // Interval for detecting intended element activation
        var i = 0;
        
        // Change display status of subitems when hovering
        function doHover(obj){
            if ($(obj).html() == undefined) 
                obj = this;
            
            // Change only one display status at a time
            if (!thislist.is(':animated')) {
                var newelem = $(obj).parent().children('ul');
                var oldelem = $(obj).parent().parent().children('li').children('ul:visible');
                if (options.keepheight == 'true') {
                    thisheight = maxheight;
                }
                else {
                    thisheight = newelem.height();
                }
                
                // Change display status if not already open
                if (!newelem.is(':visible')) {
                    newelem.children().hide();
                    newelem.animate({
                        height: thisheight
                    }, {
                        step: function(n, fx){
                            newelem.height(thisheight - n);
                        },
                        duration: options.speed
                    }).children().show();
                    
                    oldelem.animate({
                        height: 'hide'
                    }, {
                        step: function(n, fx){
                            newelem.height(thisheight - n);
                        },
                        duration: options.speed
                    }).children().hide();
                    
                    // Switch classes for headers
                    oldelem.parent().children('a').addClass(options.closed).removeClass(options.opened);
                    newelem.parent().children('a').addClass(options.opened).removeClass(options.closed);
                }
            }
        };
        
        var itemNo = 0;
        var maxheight = 0;
        
        // Setup initial state and hover events
        $(this).children('li').each(function(){
            var thisitem = $(this);
            
            itemNo++;
            
            // Set current link to current URL to 'active' and disable anchor links
            var thislink = thisitem.children('a');
            
            if (thislink.length > 0) {
                // Hover effect for all links
                thislink.hover(function(){
                    $(this).addClass(options.hover);
                }, function(){
                    $(this).removeClass(options.hover);
                });
                
                var thishref = thislink.attr('href');
                
                if (thishref == '#') {
                    // Add a click event if the header does not contain a link
                    thislink.click(function(){
                        doHover(this);
                        this.blur();
                        return false;
                    });
                }
                else 
                    if (options.activateitem == 'true' && thisurl.indexOf(thishref) > 0 && thisurl.length - thisurl.lastIndexOf(thishref) == thishref.length) {
                        thislink.parent().addClass(options.active);
                    }
            }
            
            var thischild = thisitem.children('ul');
            
            // Initialize subitems
            if (thischild.length > 0) {
                if (maxheight < thischild.height()) 
                    maxheight = thischild.height();
                
                // Change appearance of the header element of the active item
                thischild.children('li.' + options.active).parent().parent().children('a').addClass(options.header);
                
                // Bind hover events to all subitems
                thislink.hover(function(){
                    var t = this;
                    i = setInterval(function(){
                        doHover(t);
                        clearInterval(i);
                    }, 400);
                }, function(){
                    clearInterval(i);
                });
                
                
                // Set current link to current URL to 'active'
                if (options.activateitem == 'true') {
                    thischild.children('li').each(function(){
                        var m = $(this).children('a').attr('href');
                        if (m) {
                            if (thisurl.indexOf(m) > 0 && thisurl.length - thisurl.lastIndexOf(m) == m.length) {
                                $(this).addClass(options.active).parent().parent().children('a').addClass(options.opened);
                            }
                        }
                    });
                }
                else 
                    if (parseInt(options.activateitem) == itemNo) {
                        thisitem.addClass(options.active).children('a').addClass(options.opened);
                    }
            }
            
            // Close all subitems except for those with active items
            thischild.not($(this).parent().children('li.' + options.active).children('ul')).not(thischild.children('li.' + options.active).parent()).hide().parent().children('a').addClass(options.closed);
        });
        
        return this;
    };
})(jQuery);



/*************************************************************
* NLB Background Color Fader v1.0
* Author: Justin Barlow - www.netlobo.com
*
* Description:
* The Background Color Fader allows you to gradually fade the
* background of any HTML element.
*
* Usage:
* Call the Background Color Fader as follows:
*   NLBfadeBg( elementId, startBgColor, endBgColor, fadeTime );
*
* Description of Parameters
*   elementId - The id of the element you wish to fade the
*             background of.
*   startBgColor - The background color you wish to start the
*             fade from.
*   endBgColor - The background color you want to fade to.
*   fadeTime - The duration of the fade in milliseconds.
*************************************************************/

var nlbFade_hextable = [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F' ]; // used for RGB to Hex and Hex to RGB conversions
var nlbFade_elemTable = new Array( ); // global array to keep track of faded elements
var nlbFade_t = new Array( ); // global array to keep track of fading timers
function NLBfadeBg( elementId, startBgColor, endBgColor, fadeTime )
{
	var timeBetweenSteps = Math.round( Math.max( fadeTime / 300, 30 ) );
	var nlbFade_elemTableId = nlbFade_elemTable.indexOf( elementId );
	if( nlbFade_elemTableId > -1 )
	{
		for( var i = 0; i < nlbFade_t[nlbFade_elemTableId].length; i++ )
			clearTimeout( nlbFade_t[nlbFade_elemTableId][i] );
	}
	else
	{
		nlbFade_elemTable.push( elementId );
		nlbFade_elemTableId = nlbFade_elemTable.indexOf( elementId );
	}
	var startBgColorRGB = hexToRGB( startBgColor );
	var endBgColorRGB = hexToRGB( endBgColor );
	var diffRGB = new Array( );
	for( var i = 0; i < 3; i++ )
		diffRGB[i] = endBgColorRGB[i] - startBgColorRGB[i];
	var steps = Math.ceil( fadeTime / timeBetweenSteps );
	var nlbFade_s = new Array( );
	for( var i = 1; i <= steps; i++ )
	{
		var changes = new Array( );
		for( var j = 0; j < diffRGB.length; j++ )
			changes[j] = startBgColorRGB[j] + Math.round( ( diffRGB[j] / steps ) * i );
		if( i == steps )
			nlbFade_s[i - 1] = setTimeout( 'document.getElementById("'+elementId+'").style.backgroundColor = "'+endBgColor+'";', timeBetweenSteps*(i-1) );
		else
			nlbFade_s[i - 1] = setTimeout( 'document.getElementById("'+elementId+'").style.backgroundColor = "'+RGBToHex( changes )+'";', timeBetweenSteps*(i-1) );
	}
	nlbFade_t[nlbFade_elemTableId] = nlbFade_s;
}
function hexToRGB( hexVal )
{
	hexVal = hexVal.toUpperCase( );
	if( hexVal.substring( 0, 1 ) == '#' )
		hexVal = hexVal.substring( 1 );
	var hexArray = new Array( );
	var rgbArray = new Array( );
	hexArray[0] = hexVal.substring( 0, 2 );
	hexArray[1] = hexVal.substring( 2, 4 );
	hexArray[2] = hexVal.substring( 4, 6 );
	for( var k = 0; k < hexArray.length; k++ )
	{
		var num = hexArray[k];
		var res = 0;
		var j = 0;
		for( var i = num.length - 1; i >= 0; i-- )
			res += parseInt( nlbFade_hextable.indexOf( num.charAt( i ) ) ) * Math.pow( 16, j++ );
		rgbArray[k] = res;
	}
	return rgbArray;
}
function RGBToHex( rgbArray )
{
	var retval = new Array( );
	for( var j = 0; j < rgbArray.length; j++ )
	{
		var result = new Array( );
		var val = rgbArray[j];
		var i = 0;
		while( val > 16 )
		{
			result[i++] = val%16;
			val = Math.floor( val/16 );
		}
		result[i++] = val%16;
		var out = '';
		for( var k = result.length - 1; k >= 0; k-- )
			out += nlbFade_hextable[result[k]];
		retval[j] = padLeft( out, '0', 2 );
	}
	out = '#';
	for( var i = 0; i < retval.length; i++ )
		out += retval[i];
	return out;
}
if (!Array.prototype.indexOf) {
	Array.prototype.indexOf = function( val, fromIndex ) {
		if( typeof( fromIndex ) != 'number' ) fromIndex = 0;
		for( var index = fromIndex, len = this.length; index < len; index++ )
			if( this[index] == val ) return index;
		return -1;
	}
}
function padLeft( string, character, paddedWidth )
{
	if( string.length >= paddedWidth )
		return string;
	else
	{
		while( string.length < paddedWidth )
			string = character + string;
	}
	return string;
}
