// Make columns heights equal
function equalHeight(group) {
    tallest = 0;
    group.each(function() {
        thisHeight = $(this).height();
        if(thisHeight > tallest) {
            tallest = thisHeight;
        }
    });
    group.height(tallest);
}
// function to open a pop-up window with specific width
function openPopUpWindow(URL) {
	aWindow=window.open(URL,"newwindow","toolbar=no,scrollbars=no,status=no,location=no,resizable=no,menubar=no,width=650,height=500,left=400,top=100");
}

// function to open a pop-up window with specific width
function openPopUpWindowWithOptions(URL, width, height) {
	aWindow=window.open(URL,"newwindow","toolbar=no,scrollbars=no,status=no,location=no,resizable=no,menubar=no,width="+width+",height="+height+",left=400,top=100");
}
// function to obsfuscate email addresses from spammers
function contactUnobsfuscate() {
	
	// find all links in HTML
	var link = document.getElementsByTagName && document.getElementsByTagName("a");
	var contact, e;
	
	// examine all links
	for (e = 0; link && e < link.length; e++) {
	
		// does the link have use a class named "contact"
		if ((" "+link[e].className+" ").indexOf(" contact ") >= 0) {
		
			// get the obfuscated contact address
			contact = link[e].firstChild.nodeValue.toLowerCase() || "";
			
			// transform into real contact address
			contact = contact.replace(/dot/ig, ".");
			contact = contact.replace(/\(at\)/ig, "@");
			contact = contact.replace(/\s/g, "");
			
			// is contact valid?
			if (/^[^@]+@[a-z0-9]+([_\.\-]{0,1}[a-z0-9]+)*([\.]{1}[a-z0-9]+)+$/.test(contact)) {
			
				// change into a real mailto link
				link[e].href = "mailto:" + contact;
				link[e].firstChild.nodeValue = contact;
		
			}
		}
	}
}
window.onload = contactUnobsfuscate;

//close popup window and reload parent
function closeWindowAndReloadParent(){
	$(window).unload(function () { 
		//alert("Bye now!"); 
		window.opener.location.reload();			
	});
}

//if the default search term is present, clear it
function clearValue(fieldid) {
	if($("#" + fieldid).val() == $("#" + fieldid).attr('title')) {
		$("#" + fieldid).val('');
	} 
}
// if the default search term is empty, set it
function setValue(fieldid) {
	if ($("#" + fieldid).val() == "") {
		$("#" + fieldid).val($("#" + fieldid).attr('title'));
	} 
}

/*
 * Disable the fields specified and add the class disabledfield
 * 
 * @param fieldid - The ID of the field to be disabled
 */
/*
 * Disable the fields specified and add the class disabledfield
 * 
 * @param fieldid - The ID of the field to be disabled
 */
function disableField(fieldid) {
	// disable the respective field and add the class disabled field		
	$("#" + fieldid).attr("disabled", "disabled").addClass('disabledfield');
} 
/*
 * Disable the fields specified and add the class disabledfield
 * 
 * @param fieldids - The IDs of the fields to be disabled
 */
function multipleDisableField(fieldids) {	
	var fieldsarray = fieldids.replace(/ /g,'').split(',');
	// alert(fieldsarray);
	$.each(fieldsarray, function(index, value) {
		// alert(index + ': ' + value);
		disableField(value)
	});
}
/*
 * Enable the fields specified and remove the class disabledfield
 * 
 * @param fieldid - The ID of the field to be enabled
 */
function enableField(fieldid) {
	// // hide the respective field and remove the class disabled field
	$("#" + fieldid).attr("disabled", false).removeClass('disabledfield');	
} 
/*
 * Enable the multiple fields specified and remove the class disabledfield
 * 
 * @param fieldids - The IDs of the fields to be enabled
 */ 
function multipleEnableField(fieldids) {	
	var fieldsarray = fieldids.replace(/ /g,'').split(',');
	// alert(fieldsarray);
	$.each(fieldsarray, function(index, value) {
		// alert(index + ': ' + value);
		enableField(value)
	});
}
/*
 * Hide the container with the specified id and disable all input fields in it
 * 
 * @param fieldid - The ID of the container to be hidden
 */
function disableContainerByID(fieldid) {
	// hide the respective tbody and disable any HTML controls
	$("#" + fieldid).hide();
	$("#" + fieldid + " input, #" + fieldid + " select, #" + fieldid + " textarea").attr("disabled", true);
} 
/*
 * Hide the containers with the specified id and disable all input fields in it
 * 
 * @param fieldids - The IDs of the containers to be hidden
 */
function multipleDisableContainerByID(fieldids) {	
	var fieldsarray = fieldids.replace(/ /g,'').split(',');
	// alert(fieldsarray);
	$.each(fieldsarray, function(index, value) {
		// alert(index + ': ' + value);
		disableContainerByID(value)
	});
}
/*
 * Show the container with the specified id and enable all input fields in it
 * 
 * @param fieldid - The ID of the container to be shown
 */
function enableContainerByID(fieldid) {
	// hide the respective tbody and disable any HTML controls
	$("#" + fieldid).show();
	$("#" + fieldid + " input, #" + fieldid + " select, #" + fieldid + " textarea").attr("disabled", false);
}
 /*
 * Show the container with the specified id and enable all input fields in it
 * 
 * @param fieldids - The IDs of the containers to be shown
 */
function multipleEnableContainerByID(fieldids) {	
	var fieldsarray = fieldids.replace(/ /g,'').split(',');
	// alert(fieldsarray);
	$.each(fieldsarray, function(index, value) {
		// alert(index + ': ' + value);
		enableContainerByID(value)
	});
}
 /*
 * Hide the container with the specified class and disable all input fields in it
 * 
 * @param fieldid - The class of the container to be hidden
 */
function disableContainerByClass(classid) {
	// hide the respective tbody and disable any HTML controls
	$("." + classid).hide();
	$("." + classid + " input, ." + classid + " select, ." + classid + " textarea").attr("disabled", true);
} 
/*
 * Hide the container with the specified class and disable all input fields in it
 * 
 * @param fieldids - The class of the containers to be hidden
 */
function multipleDisableContainerByClass(classids){
	var fieldsarray = classids.replace(/ /g,'').split(',');
	// alert(fieldsarray);
	$.each(fieldsarray, function(index, value) {
		// alert(index + ': ' + value);
		disableContainerByClass(value)
	});
}
/*
 * Show the container with the specified class and enable all input fields in it
 * 
 * @param fieldid - The class of the container to be shown
 */
function enableContainerByClass(classid) {
	// hide the respective tbody and disable any HTML controls
	$("." + classid).show();
	$("." + classid + " input, ." + classid + " select, ." + classid + " textarea").attr("disabled", false);
}
/*
 * Show the container with the specified class and enable all input fields in it
 * 
 * @param fieldids - The class of the containers to be shown
 */
function multipleEnableContainerByClass(classids){
	var fieldsarray = classids.replace(/ /g,'').split(',');
	// alert(fieldsarray);
	$.each(fieldsarray, function(index, value) {
		// alert(index + ': ' + value);
		enableContainerByClass(value)
	});
}
/**
 * Resize the contents of the body to fit due to removal or addition of elements
 * which affect the height of the page
 */
function resizeContentForm() {
	equalHeight($("#contentcolumn, #leftcolumn, #rightcolumn"));
	$("#contentcolumn").css({'height': $("#contentcolumn").height() + 150});		
} 
function resizePageForm() {		
	equalHeight($("form, #contentcolumn"));
	$("#contentcolumn").css({'height': $("form").height() + 50});	
} 
// function to do nothing
function doNothing(){	
}
// remove empty and null values
function cleanArray(actual){
  var newArray = new Array();
  for(var i = 0; i<actual.length; i++){
      if (actual[i]){
        newArray.push(actual[i]);
    }
  }
  return newArray;
}
// remove duplicate values from array
function removeDuplicates(inputArray) {
	var i;
	var len = inputArray.length;
	var outputArray = [];
	var temp = {};

	for (i = 0; i < len; i++) {
		temp[inputArray[i]] = 0;
	}
	for (i in temp) {
		outputArray.push(i);
	}
	return outputArray;
}
// count occurence of a value in an array
function countValuesInArray(myArray, val){
	var results = new Array();
	for (var j=0; j<myArray.length; j++) {
		var key = myArray[j].toString(); // make it an associative array
		if (!results[key]) {
			results[key] = 1
		} else {
			results[key] = results[key] + 1;
		}
	}
	return results[val];
}
// general purpose function to see if an input value has been
// entered at all
function isEmptyString(inputStr) {
	if (inputStr == null || inputStr == "") {
		return true;
	}
	return false;
}
// base 64 javascript encode
function base64_encode (data) {    
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        enc = "",
        tmp_arr = [];

    if (!data) {
        return data;
    }

    data = this.utf8_encode(data + '');
    do { // pack three octets into four hexets
        o1 = data.charCodeAt(i++);
        o2 = data.charCodeAt(i++);
        o3 = data.charCodeAt(i++);

        bits = o1 << 16 | o2 << 8 | o3;

        h1 = bits >> 18 & 0x3f;
        h2 = bits >> 12 & 0x3f;
        h3 = bits >> 6 & 0x3f;
        h4 = bits & 0x3f;

        // use hexets to index into b64, and append result to encoded string
        tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
    } while (i < data.length);

    enc = tmp_arr.join('');
    var r = data.length % 3;
    return (r ? enc.slice(0, r - 3) : enc) + '==='.slice(r || 3);
}
// utf8 javascript encode
function utf8_encode (argString) {
    if (argString === null || typeof argString === "undefined") {
        return "";
    }

    var string = (argString + ''); // .replace(/\r\n/g, "\n").replace(/\r/g, "\n");
    var utftext = "",
        start, end, stringl = 0;

    start = end = 0;
    stringl = string.length;
    for (var n = 0; n < stringl; n++) {
        var c1 = string.charCodeAt(n);
        var enc = null;

        if (c1 < 128) {
            end++;
        } else if (c1 > 127 && c1 < 2048) {
            enc = String.fromCharCode((c1 >> 6) | 192) + String.fromCharCode((c1 & 63) | 128);
        } else {
            enc = String.fromCharCode((c1 >> 12) | 224) + String.fromCharCode(((c1 >> 6) & 63) | 128) + String.fromCharCode((c1 & 63) | 128);
        }
        if (enc !== null) {
            if (end > start) {
                utftext += string.slice(start, end);
            }
            utftext += enc;
            start = end = n + 1;
        }
    }

    if (end > start) {
        utftext += string.slice(start, stringl);
    }

    return utftext;
}
// base 64 javascript decode
function base64_decode (data) {    
    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
        ac = 0,
        dec = "",
        tmp_arr = [];

    if (!data) {
        return data;
    }

    data += '';
    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join('');
    dec = this.utf8_decode(dec);

    return dec;
}
// utf8 javascript decode
function utf8_decode (str_data) {
    var tmp_arr = [],
        i = 0,
        ac = 0,
        c1 = 0,
        c2 = 0,
        c3 = 0;

    str_data += '';

    while (i < str_data.length) {
        c1 = str_data.charCodeAt(i);
        if (c1 < 128) {
            tmp_arr[ac++] = String.fromCharCode(c1);
            i++;
        } else if (c1 > 191 && c1 < 224) {
            c2 = str_data.charCodeAt(i + 1);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
            i += 2;
        } else {
            c2 = str_data.charCodeAt(i + 1);
            c3 = str_data.charCodeAt(i + 2);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }
    }

    return tmp_arr.join('');
} 
function passwordStrength(password)
{
	var desc = new Array();
	desc[0] = "Very Weak";
	desc[1] = "Weak";
	desc[2] = "Better";
	desc[3] = "Medium";
	desc[4] = "Strong";
	desc[5] = "Strongest";

	var score   = 0;

	//if password bigger than 6 give 1 point
	if (password.length > 6) score++;

	//if password has both lower and uppercase characters give 1 point	
	if ( ( password.match(/[a-z]/) ) && ( password.match(/[A-Z]/) ) ) score++;

	//if password has at least one number give 1 point
	if (password.match(/\d+/)) score++;

	//if password has at least one special caracther give 1 point
	if ( password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/) )	score++;

	//if password bigger than 12 give another 1 point
	if (password.length > 12) score++;

	 document.getElementById("passwordDescription").innerHTML = desc[score];
	 document.getElementById("passwordStrength").className = "strength" + score;
}
function validateEmail(email) {
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    if( !emailReg.test( email ) ) {
        return false;
    } else {
        return true;
    }
}
function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return pattern.test(emailAddress);
}
// check that price is a valid number
function IsValidAmount(value){
	var ValidChars = "0123456789";
	var valid=true;
	var Char;
	
	for (i = 0; i < value.length && valid == true; i++){ 
		Char = value.charAt(i); 
		if (ValidChars.indexOf(Char) == -1) {
			valid = false;
		}
	}
	return valid;
}
function IsValidDecimal(value){
	var ValidChars = ".0123456789";
	var valid=true;
	var Char;
	
	for (i = 0; i < value.length && valid == true; i++){ 
		Char = value.charAt(i); 
		if (ValidChars.indexOf(Char) == -1) {
			valid = false;
		}
	}
	return valid;
}
function isAlpha(value){
	var valid=true;
	var Char;
	var regexp = /^[a-zA-Z0-9-_]*$/;
	if (value.search(regexp) == -1) {
		valid=false;
	}
	return valid;
}
function isUgNumber(number){
	var valid=true;
	var firstchars = number.substring(0, 5);
	var allowed = new Array("25677","25678","25670","25671","25675","25679","25673");
	if($.inArray(firstchars, allowed) == -1) {
		valid=false;
	}
	return valid;
}
function addCommas(nStr) {
    nStr += '';
    var x = nStr.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}
// thousand separtor for amount fields
function formatAmount(num,dec,thou,pnt,curr1,curr2,n1,n2) {
	var x = Math.round(num * Math.pow(10,dec));
	if (x >= 0) n1=n2='';
	var y = (''+Math.abs(x)).split('');
	var z = y.length - dec; 
	if (z<0) z--; 
	for(var i = z; i < 0; i++) 
	y.unshift('0'); 
	if (z<0) z = 1; 
	y.splice(z, 0, pnt); 
	if(y[0] == pnt) y.unshift('0'); 
	while (z > 3) {
		z-=3; 
		y.splice(z,0,thou);
	}
	var r = curr1+n1+y.join('')+n2+curr2;return r;
}
Date.prototype.monthNames = [
	"January", "February", "March",
	"April", "May", "June",
	"July", "August", "September",
	"October", "November", "December"
];
Date.prototype.getMonthName = function() {
	return this.monthNames[this.getMonth()];
};
Date.prototype.getShortMonthName = function () {
	return this.getMonthName().substr(0, 3);
};
function sha1(str) {
  // http://kevin.vanzonneveld.net
  // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
  // + namespaced by: Michael White (http://getsprink.com)
  // +      input by: Brett Zamir (http://brett-zamir.me)
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // -    depends on: utf8_encode
  // *     example 1: sha1('Kevin van Zonneveld');
  // *     returns 1: '54916d2e62f65b3afa6e192e6a601cdbe5cb5897'
  var rotate_left = function (n, s) {
    var t4 = (n << s) | (n >>> (32 - s));
    return t4;
  };

/*var lsb_hex = function (val) { // Not in use; needed?
    var str="";
    var i;
    var vh;
    var vl;

    for ( i=0; i<=6; i+=2 ) {
      vh = (val>>>(i*4+4))&0x0f;
      vl = (val>>>(i*4))&0x0f;
      str += vh.toString(16) + vl.toString(16);
    }
    return str;
  };*/

  var cvt_hex = function (val) {
    var str = "";
    var i;
    var v;

    for (i = 7; i >= 0; i--) {
      v = (val >>> (i * 4)) & 0x0f;
      str += v.toString(16);
    }
    return str;
  };

  var blockstart;
  var i, j;
  var W = new Array(80);
  var H0 = 0x67452301;
  var H1 = 0xEFCDAB89;
  var H2 = 0x98BADCFE;
  var H3 = 0x10325476;
  var H4 = 0xC3D2E1F0;
  var A, B, C, D, E;
  var temp;

  str = this.utf8_encode(str);
  var str_len = str.length;

  var word_array = [];
  for (i = 0; i < str_len - 3; i += 4) {
    j = str.charCodeAt(i) << 24 | str.charCodeAt(i + 1) << 16 | str.charCodeAt(i + 2) << 8 | str.charCodeAt(i + 3);
    word_array.push(j);
  }

  switch (str_len % 4) {
  case 0:
    i = 0x080000000;
    break;
  case 1:
    i = str.charCodeAt(str_len - 1) << 24 | 0x0800000;
    break;
  case 2:
    i = str.charCodeAt(str_len - 2) << 24 | str.charCodeAt(str_len - 1) << 16 | 0x08000;
    break;
  case 3:
    i = str.charCodeAt(str_len - 3) << 24 | str.charCodeAt(str_len - 2) << 16 | str.charCodeAt(str_len - 1) << 8 | 0x80;
    break;
  }

  word_array.push(i);

  while ((word_array.length % 16) != 14) {
    word_array.push(0);
  }

  word_array.push(str_len >>> 29);
  word_array.push((str_len << 3) & 0x0ffffffff);

  for (blockstart = 0; blockstart < word_array.length; blockstart += 16) {
    for (i = 0; i < 16; i++) {
      W[i] = word_array[blockstart + i];
    }
    for (i = 16; i <= 79; i++) {
      W[i] = rotate_left(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1);
    }


    A = H0;
    B = H1;
    C = H2;
    D = H3;
    E = H4;

    for (i = 0; i <= 19; i++) {
      temp = (rotate_left(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
      E = D;
      D = C;
      C = rotate_left(B, 30);
      B = A;
      A = temp;
    }

    for (i = 20; i <= 39; i++) {
      temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
      E = D;
      D = C;
      C = rotate_left(B, 30);
      B = A;
      A = temp;
    }

    for (i = 40; i <= 59; i++) {
      temp = (rotate_left(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
      E = D;
      D = C;
      C = rotate_left(B, 30);
      B = A;
      A = temp;
    }

    for (i = 60; i <= 79; i++) {
      temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
      E = D;
      D = C;
      C = rotate_left(B, 30);
      B = A;
      A = temp;
    }

    H0 = (H0 + A) & 0x0ffffffff;
    H1 = (H1 + B) & 0x0ffffffff;
    H2 = (H2 + C) & 0x0ffffffff;
    H3 = (H3 + D) & 0x0ffffffff;
    H4 = (H4 + E) & 0x0ffffffff;
  }

  temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);
  return temp.toLowerCase();
}
function exportTableToCSV($table, filename) {
	var $rows = $table.find('tr:has(td)'),

	// Temporary delimiter characters unlikely to be typed by keyboard
	// This is to avoid accidentally splitting the actual contents
	tmpColDelim = String.fromCharCode(11), // vertical tab character
	tmpRowDelim = String.fromCharCode(0), // null character

	// actual delimiter characters for CSV format
	colDelim = '","',
	rowDelim = '"\r\n"',

	// Grab text from table into CSV formatted string
	csv = '"' + $rows.map(function (i, row) {
		var $row = $(row),
			$cols = $row.find('td');

		return $cols.map(function (j, col) {
			var $col = $(col),
				text = $col.text();

			return text.replace('"', '""'); // escape double quotes

		}).get().join(tmpColDelim);

	}).get().join(tmpRowDelim)
		.split(tmpRowDelim).join(rowDelim)
		.split(tmpColDelim).join(colDelim) + '"',

	// Data URI
	csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);

	$(this)
		.attr({
		'download': filename,
			'href': csvData,
			'target': '_blank'
	});
}
function trim(str) {
   return str.replace(/^\s+|\s+$/g,"");
}
function autoPopulateSelectChain(fieldid, target, url, current){
	var fieldvalue = $('#'+fieldid).val();
	var currentvalue = current; // alert('cur '+currentvalue);
	if(!isEmptyString(fieldvalue)){
		var posturl = url+fieldvalue;
		$("#"+target).find("option:gt(0)").remove().trigger("change").trigger("chosen:updated");
		$("#"+target).find("option:first").text("Loading...").trigger("change").trigger("chosen:updated");
		$.getJSON(posturl, function (data) {
			$("#"+target).find("option:first").text("<Select One>").attr("value","").attr("selected", true);
			selectValues = data;
			$.each(selectValues, function(key, value) {
				if(!isEmptyString(currentvalue) && currentvalue == key){
					$("#"+target).append($("<option></option>").attr("value",key).text(value).attr("selected", true).trigger("change").trigger("chosen:updated"));
				} else {
					$("#"+target).append($("<option></option>").attr("value",key).text(value).trigger("change").trigger("chosen:updated"));
				}
			});
			$("#"+target).trigger("change").trigger("chosen:updated");
		});
	}
}
function stripTrailingSlash(str) {
	if(str.substr(-1) == '/') {
		return str.substr(0, str.length - 1);
	}
	return str;
}
Date.prototype.monthNames = [
	"January", "February", "March",
	"April", "May", "June",
	"July", "August", "September",
	"October", "November", "December"
];
Date.prototype.getMonthName = function() {
	return this.monthNames[this.getMonth()];
};
Date.prototype.getShortMonthName = function () {
	return this.getMonthName().substr(0, 3);
};
Date.prototype.addDays = function(days) {
	this.setDate(this.getDate() + days);
	return this;
};
function addDays(theDate, days) {
	return new Date(theDate.getTime() + days*24*60*60*1000);
}
function pad(n) {
	return (n < 10) ? ("0" + n) : n;
}
function getMonthShortName(mon){
	var month = new Array();
	month[0] = "Jan";
	month[1] = "Feb";
	month[2] = "Mar";
	month[3] = "Apr";
	month[4] = "May";
	month[5] = "Jun";
	month[6] = "Jul";
	month[7] = "Aug";
	month[8] = "Sep";
	month[9] = "Oct";
	month[10] = "Nov";
	month[11] = "Dec";
	return month[mon];
}
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}