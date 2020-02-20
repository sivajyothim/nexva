<?php 
session_start();
include"../config.inc.php";
include"../function.inc.php";
verifyAuth();

// Call logout function
if(isset($_GET['action']) && $_GET['action'] == "logout") {
logout();	
}

// List Menu UpHops

$sql_uphops = "SELECT * FROM list_uphops";
$query_uphops = mysql_query($sql_uphops);

$sql_emuse = "SELECT * FROM list_emusemm";
$query_emuse = mysql_query($sql_emuse);

$sql_emm = "SELECT * FROM list_emusemm";
$query_emm = mysql_query($sql_emm);

$sql_reshare = "SELECT * FROM list_reshare";
$query_reshare = mysql_query($sql_reshare);

$sql_clrows = "SELECT * FROM cccam_channelinfo GROUP BY chan_provider";
$query_clrows = mysql_query($sql_clrows);
$num_rows = mysql_num_rows($query_clrows);

$sql_chan = "SELECT * FROM cccam_channelinfo";
$query_chan = mysql_query($sql_chan);

$sql_time_from = "SELECT * FROM list_time";
$query_time_from = mysql_query($sql_time_from);

$sql_time_to = "SELECT * FROM list_time";
$query_time_to = mysql_query($sql_time_to);

$sql_server_list = "SELECT * FROM cccam_server_list";
$query_server = mysql_query($sql_server_list);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript" language="javascript">
function Change()
{
	
if (document.form.check1.checked == true) 
  { 
  document.getElementById("quote_to").style.color="red";
  document.form.quote_to.disabled = true;
  document.form.quote_to.style.backgroundColor="#CCC";
  document.form.quote_to.value = "Unlimited";
  
  document.getElementById("quote_value").style.color="red";
  document.form.quote_value.disabled = true;
  document.form.quote_value.style.backgroundColor="#CCC";
  document.form.quote_value.value = "Unlimited";
  
  document.getElementById("quote_from").style.color="red";
  document.form.quote_from.disabled = true;
  document.form.quote_from.style.backgroundColor="#CCC";
  document.form.quote_from.value = "Unlimited";
  
  }
else
  {
  document.getElementById("quote_to").style.color="#000";
  document.form.quote_to.disabled = false;
  document.form.quote_to.style.backgroundColor="#FFC";
  document.form.quote_to.value = "";
  
  document.getElementById("quote_value").style.color="#000";
  document.form.quote_value.disabled = false; 
  document.form.quote_value.style.backgroundColor="#FFC";
  document.form.quote_value.value = "";
  
  document.getElementById("quote_from").style.color="#000";
  document.form.quote_from.disabled = false;
  document.form.quote_from.style.backgroundColor="#FFC";
  document.form.quote_from.value = "<?php echo date("d-m-Y");?>";
  }
}
function ChangeFTP()
{
	
if (document.form.ftp_active.value == "0") 
  { 
  document.getElementById("ftp_active").style.color="#000";
  document.form.ftp_active.disabled = false;
  document.form.ftp_active.style.backgroundColor="#FFC";
  document.form.ftp_active.value = "n/a";
  
  document.form.ftp_ip.style.color="red";
  document.form.ftp_ip.disabled = true;
  document.form.ftp_ip.style.backgroundColor="#CCC";
  document.form.ftp_ip.value = "n/a";
  
  document.form.ftp_port.style.color="red";
  document.form.ftp_port.disabled = true;
  document.form.ftp_port.style.backgroundColor="#CCC";
  document.form.ftp_port.value = "n/a";
  
  document.form.ftp_user.style.color="red";
  document.form.ftp_user.disabled = true;
  document.form.ftp_user.style.backgroundColor="#CCC";
  document.form.ftp_user.value = "n/a";
  
  document.form.ftp_pass.style.color="red";
  document.form.ftp_pass.disabled = true;
  document.form.ftp_pass.style.backgroundColor="#CCC";
  document.form.ftp_pass.value = "n/a";
  
  document.form.ftp_remote.style.color="red";
  document.form.ftp_remote.disabled = true;
  document.form.ftp_remote.style.backgroundColor="#CCC";
  document.form.ftp_remote.value = "n/a";
  
  document.form.ftp_local.style.color="red";
  document.form.ftp_local.disabled = true;
  document.form.ftp_local.style.backgroundColor="#CCC";
  document.form.ftp_local.value = "n/a";
  
  document.form.server_id.style.color="red";
  document.form.server_id.disabled = true;
  document.form.server_id.style.backgroundColor="#CCC";
  }
else
  {
  document.form.ftp_active.style.color="#000";
  document.form.ftp_active.disabled = false;
  document.form.ftp_active.style.backgroundColor="#FFC";
  document.form.ftp_active.value = "";
  
  document.form.ftp_ip.style.color="#000";
  document.form.ftp_ip.disabled = false;
  document.form.ftp_ip.style.backgroundColor="#FFC";
  document.form.ftp_ip.value = "";
  
  document.form.ftp_port.style.color="#000";
  document.form.ftp_port.disabled = false;
  document.form.ftp_port.style.backgroundColor="#FFC";
  document.form.ftp_port.value = "";
  
  document.form.ftp_user.style.color="#000";
  document.form.ftp_user.disabled = false;
  document.form.ftp_user.style.backgroundColor="#FFC";
  document.form.ftp_user.value = "";
  
  document.form.ftp_pass.style.color="#000";
  document.form.ftp_pass.disabled = false;
  document.form.ftp_pass.style.backgroundColor="#FFC";
  document.form.ftp_pass.value = "";
  
  document.form.ftp_remote.style.color="#000";
  document.form.ftp_remote.disabled = false;
  document.form.ftp_remote.style.backgroundColor="#FFC";
  document.form.ftp_remote.value = "";
  
  document.form.ftp_local.style.color="#000";
  document.form.ftp_local.disabled = false;
  document.form.ftp_local.style.backgroundColor="#FFC";
  document.form.ftp_local.value = "";
  
  document.form.server_id.style.color="#000";
  document.form.server_id.disabled = false;
  document.form.server_id.style.backgroundColor="#FFC";
  }
}


function nameempty()
{
        if ( document.getElementById("user_name").value == '' || document.getElementById("user_username").value == '' || document.getElementById("user_password").value == '' || document.getElementById("quote_value").value == '' || document.getElementById("quote_to").value == '' )
        {
                alert('Please enter : Name, Username, Password, Quote!!!')
                return false;
        }
}
function choice(arg)
//return random index number in valid range of arg array
{
        return Math.floor(Math.random()*arg.length);
}

function randstr(arg)
//return random argument of arg array
{
var str = '';
var seed = choice(arg);
        str = arg[seed];
return str;
}

function initialize()
//use actual time to initialize random function as javascript doesn't provide an initialization function itself
//to get more random, use getMilliseconds() function
//don't use getTime() as it produces numbers larger than 1000 billions, eheh
{
        var count=new Date().getSeconds();
        for (c=0; c<count; c++)
                Math.random();
}

function mkpass()
{
        //use of initialize() can decrease speed of script. On really slow systems, disable it.
        initialize();

        //password length
        var pass_len=8;

        var cons_lo = ['b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z'];
        var cons_up = ['B','C','D','F','G','H','J','K','L','M','N','P','Q','R','S','T','V','W','X','Y','Z'];
        var hard_cons_lo = ['b','c','d','f','g','h','k','m','p','s','t','v','z'];
        var hard_cons_up = ['B','C','D','F','G','H','K','M','P','S','T','V','Z'];
var link_cons_lo = ['h','l','r'];
var link_cons_up = ['H','L','R'];
var vowels_lo = ['a','e','i','o','u'];
var vowels_up = ['A','E','I','U']; //O (letter o) and 0 (number zero) get confused
        var digits = ['1','2','3','4','5','6','7','8','9'];

        //change at will how many times digits appears in names array. Order doesn't matter
        var names = [cons_lo, cons_up, digits, hard_cons_lo, hard_cons_up, digits, link_cons_lo, link_cons_up, digits, vowels_lo, vowels_up, digits];

var newpass= '';
        for(i=0; i<pass_len; i++)
         newpass = newpass + randstr(names[choice(names)]);

return newpass;
}

/**
This is a JavaScript library that will allow you to easily add some basic DHTML
drop-down datepicker functionality to your Notes forms. This script is not as
full-featured as others you may find on the Internet, but it's free, it's easy to
understand, and it's easy to change.

You'll also want to include a stylesheet that makes the datepicker elements
look nice. An example one can be found in the database that this script was
originally released with, at:

http://www.nsftools.com/tips/NotesTips.htm#datepicker

I've tested this lightly with Internet Explorer 6 and Mozilla Firefox. I have no idea
how compatible it is with other browsers.

version 1.5
December 4, 2005
Julian Robichaux -- http://www.nsftools.com

HISTORY
--  version 1.0 (Sept. 4, 2004):
Initial release.

--  version 1.1 (Sept. 5, 2004):
Added capability to define the date format to be used, either globally (using the
defaultDateSeparator and defaultDateFormat variables) or when the displayDatePicker
function is called.

--  version 1.2 (Sept. 7, 2004):
Fixed problem where datepicker x-y coordinates weren't right inside of a table.
Fixed problem where datepicker wouldn't display over selection lists on a page.
Added a call to the datePickerClosed function (if one exists) after the datepicker
is closed, to allow the developer to add their own custom validation after a date
has been chosen. For this to work, you must have a function called datePickerClosed
somewhere on the page, that accepts a field object as a parameter. See the
example in the comments of the updateDateField function for more details.

--  version 1.3 (Sept. 9, 2004)
Fixed problem where adding the <div> and <iFrame> used for displaying the datepicker
was causing problems on IE 6 with global variables that had handles to objects on
the page (I fixed the problem by adding the elements using document.createElement()
and document.body.appendChild() instead of document.body.innerHTML += ...).

--  version 1.4 (Dec. 20, 2004)
Added "targetDateField.focus();" to the updateDateField function (as suggested
by Alan Lepofsky) to avoid a situation where the cursor focus is at the top of the
form after a date has been picked. Added "padding: 0px;" to the dpButton CSS
style, to keep the table from being so wide when displayed in Firefox.

-- version 1.5 (Dec 4, 2005)
Added display=none when datepicker is hidden, to fix problem where cursor is
not visible on input fields that are beneath the date picker. Added additional null
date handling for date errors in Safari when the date is empty. Added additional
error handling for iFrame creation, to avoid reported errors in Opera. Added
onMouseOver event for day cells, to allow color changes when the mouse hovers
over a cell (to make it easier to determine what cell you're over). Added comments
in the style sheet, to make it more clear what the different style elements are for.
*/

var datePickerDivID = "datepicker";
var iFrameDivID = "datepickeriframe";

var dayArrayShort = new Array('Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa');
var dayArrayMed = new Array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
var dayArrayLong = new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
var monthArrayShort = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
var monthArrayMed = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec');
var monthArrayLong = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
 
// these variables define the date formatting we're expecting and outputting.
// If you want to use a different format by default, change the defaultDateSeparator
// and defaultDateFormat variables either here or on your HTML page.
var defaultDateSeparator = "-";        // common values would be "/" or "."
var defaultDateFormat = "dmy"    // valid values are "mdy", "dmy", and "ymd"
var dateSeparator = defaultDateSeparator;
var dateFormat = defaultDateFormat;

/**
This is the main function you'll call from the onClick event of a button.
Normally, you'll have something like this on your HTML page:

Start Date: <input name="StartDate">
<input type=button value="select" onclick="displayDatePicker('StartDate');">

That will cause the datepicker to be displayed beneath the StartDate field and
any date that is chosen will update the value of that field. If you'd rather have the
datepicker display beneath the button that was clicked, you can code the button
like this:

<input type=button value="select" onclick="displayDatePicker('StartDate', this);">

So, pretty much, the first argument (dateFieldName) is a string representing the
name of the field that will be modified if the user picks a date, and the second
argument (displayBelowThisObject) is optional and represents an actual node
on the HTML document that the datepicker should be displayed below.

In version 1.1 of this code, the dtFormat and dtSep variables were added, allowing
you to use a specific date format or date separator for a given call to this function.
Normally, you'll just want to set these defaults globally with the defaultDateSeparator
and defaultDateFormat variables, but it doesn't hurt anything to add them as optional
parameters here. An example of use is:

<input type=button value="select" onclick="displayDatePicker('StartDate', false, 'dmy', '.');">

This would display the datepicker beneath the StartDate field (because the
displayBelowThisObject parameter was false), and update the StartDate field with
the chosen value of the datepicker using a date format of dd.mm.yyyy
*/
function displayDatePicker(dateFieldName, displayBelowThisObject, dtFormat, dtSep)
{
  var targetDateField = document.getElementsByName (dateFieldName).item(0);
 
  // if we weren't told what node to display the datepicker beneath, just display it
  // beneath the date field we're updating
  if (!displayBelowThisObject)
    displayBelowThisObject = targetDateField;
 
  // if a date separator character was given, update the dateSeparator variable
  if (dtSep)
    dateSeparator = dtSep;
  else
    dateSeparator = defaultDateSeparator;
 
  // if a date format was given, update the dateFormat variable
  if (dtFormat)
    dateFormat = dtFormat;
  else
    dateFormat = defaultDateFormat;
 
  var x = displayBelowThisObject.offsetLeft;
  var y = displayBelowThisObject.offsetTop + displayBelowThisObject.offsetHeight ;
 
  // deal with elements inside tables and such
  var parent = displayBelowThisObject;
  while (parent.offsetParent) {
    parent = parent.offsetParent;
    x += parent.offsetLeft;
    y += parent.offsetTop ;
  }
 
  drawDatePicker(targetDateField, x, y);
}


/**
Draw the datepicker object (which is just a table with calendar elements) at the
specified x and y coordinates, using the targetDateField object as the input tag
that will ultimately be populated with a date.

This function will normally be called by the displayDatePicker function.
*/
function drawDatePicker(targetDateField, x, y)
{
  var dt = getFieldDate(targetDateField.value );
 
  // the datepicker table will be drawn inside of a <div> with an ID defined by the
  // global datePickerDivID variable. If such a div doesn't yet exist on the HTML
  // document we're working with, add one.
  if (!document.getElementById(datePickerDivID)) {
    // don't use innerHTML to update the body, because it can cause global variables
    // that are currently pointing to objects on the page to have bad references
    //document.body.innerHTML += "<div id='" + datePickerDivID + "' class='dpDiv'></div>";
    var newNode = document.createElement("div");
    newNode.setAttribute("id", datePickerDivID);
    newNode.setAttribute("class", "dpDiv");
    newNode.setAttribute("style", "visibility: hidden;");
    document.body.appendChild(newNode);
  }
 
  // move the datepicker div to the proper x,y coordinate and toggle the visiblity
  var pickerDiv = document.getElementById(datePickerDivID);
  pickerDiv.style.position = "absolute";
  pickerDiv.style.left = x + "px";
  pickerDiv.style.top = y + "px";
  pickerDiv.style.visibility = (pickerDiv.style.visibility == "visible" ? "hidden" : "visible");
  pickerDiv.style.display = (pickerDiv.style.display == "block" ? "none" : "block");
  pickerDiv.style.zIndex = 10000;
 
  // draw the datepicker table
  refreshDatePicker(targetDateField.name, dt.getFullYear(), dt.getMonth(), dt.getDate());
}


/**
This is the function that actually draws the datepicker calendar.
*/
function refreshDatePicker(dateFieldName, year, month, day)
{
  // if no arguments are passed, use today's date; otherwise, month and year
  // are required (if a day is passed, it will be highlighted later)
  var thisDay = new Date();
 
  if ((month >= 0) && (year > 0)) {
    thisDay = new Date(year, month, 1);
  } else {
    day = thisDay.getDate();
    thisDay.setDate(1);
  }
 
  // the calendar will be drawn as a table
  // you can customize the table elements with a global CSS style sheet,
  // or by hardcoding style and formatting elements below
  var crlf = "\r\n";
  var TABLE = "<table cols=7 class='dpTable'>" + crlf;
  var xTABLE = "</table>" + crlf;
  var TR = "<tr class='dpTR'>";
  var TR_title = "<tr class='dpTitleTR'>";
  var TR_days = "<tr class='dpDayTR'>";
  var TR_todaybutton = "<tr class='dpTodayButtonTR'>";
  var xTR = "</tr>" + crlf;
  var TD = "<td class='dpTD' onMouseOut='this.className=\"dpTD\";' onMouseOver=' this.className=\"dpTDHover\";' ";    // leave this tag open, because we'll be adding an onClick event
  var TD_title = "<td colspan=5 class='dpTitleTD'>";
  var TD_buttons = "<td class='dpButtonTD'>";
  var TD_todaybutton = "<td colspan=7 class='dpTodayButtonTD'>";
  var TD_days = "<td class='dpDayTD'>";
  var TD_selected = "<td class='dpDayHighlightTD' onMouseOut='this.className=\"dpDayHighlightTD\";' onMouseOver='this.className=\"dpTDHover\";' ";    // leave this tag open, because we'll be adding an onClick event
  var xTD = "</td>" + crlf;
  var DIV_title = "<div class='dpTitleText'>";
  var DIV_selected = "<div class='dpDayHighlight'>";
  var xDIV = "</div>";
 
  // start generating the code for the calendar table
  var html = TABLE;
 
  // this is the title bar, which displays the month and the buttons to
  // go back to a previous month or forward to the next month
  html += TR_title;
  html += TD_buttons + getButtonCode(dateFieldName, thisDay, -1, "&lt;") + xTD;
  html += TD_title + DIV_title + monthArrayLong[ thisDay.getMonth()] + " " + thisDay.getFullYear() + xDIV + xTD;
  html += TD_buttons + getButtonCode(dateFieldName, thisDay, 1, "&gt;") + xTD;
  html += xTR;
 
  // this is the row that indicates which day of the week we're on
  html += TR_days;
  for(i = 0; i < dayArrayShort.length; i++)
    html += TD_days + dayArrayShort[i] + xTD;
  html += xTR;
 
  // now we'll start populating the table with days of the month
  html += TR;
 
  // first, the leading blanks
  for (i = 0; i < thisDay.getDay(); i++)
    html += TD + "&nbsp;" + xTD;
 
  // now, the days of the month
  do {
    dayNum = thisDay.getDate();
    TD_onclick = " onclick=\"updateDateField('" + dateFieldName + "', '" + getDateString(thisDay) + "');\">";
    
    if (dayNum == day)
      html += TD_selected + TD_onclick + DIV_selected + dayNum + xDIV + xTD;
    else
      html += TD + TD_onclick + dayNum + xTD;
    
    // if this is a Saturday, start a new row
    if (thisDay.getDay() == 6)
      html += xTR + TR;
    
    // increment the day
    thisDay.setDate(thisDay.getDate() + 1);
  } while (thisDay.getDate() > 1)
 
  // fill in any trailing blanks
  if (thisDay.getDay() > 0) {
    for (i = 6; i > thisDay.getDay(); i--)
      html += TD + "&nbsp;" + xTD;
  }
  html += xTR;
 
  // add a button to allow the user to easily return to today, or close the calendar
  var today = new Date();
  var todayString = "Today is " + dayArrayMed[today.getDay()] + ", " + monthArrayMed[ today.getMonth()] + " " + today.getDate();
  html += TR_todaybutton + TD_todaybutton;
  html += "<button class='dpTodayButton' onClick='refreshDatePicker(\"" + dateFieldName + "\");'>this month</button> ";
  html += "<button class='dpTodayButton' onClick='updateDateField(\"" + dateFieldName + "\");'>close</button>";
  html += xTD + xTR;
 
  // and finally, close the table
  html += xTABLE;
 
  document.getElementById(datePickerDivID).innerHTML = html;
  // add an "iFrame shim" to allow the datepicker to display above selection lists
  adjustiFrame();
}


/**
Convenience function for writing the code for the buttons that bring us back or forward
a month.
*/
function getButtonCode(dateFieldName, dateVal, adjust, label)
{
  var newMonth = (dateVal.getMonth () + adjust) % 12;
  var newYear = dateVal.getFullYear() + parseInt((dateVal.getMonth() + adjust) / 12);
  if (newMonth < 0) {
    newMonth += 12;
    newYear += -1;
  }
 
  return "<button class='dpButton' onClick='refreshDatePicker(\"" + dateFieldName + "\", " + newYear + ", " + newMonth + ");'>" + label + "</button>";
}


/**
Convert a JavaScript Date object to a string, based on the dateFormat and dateSeparator
variables at the beginning of this script library.
*/
function getDateString(dateVal)
{
  var dayString = "00" + dateVal.getDate();
  var monthString = "00" + (dateVal.getMonth()+1);
  dayString = dayString.substring(dayString.length - 2);
  monthString = monthString.substring(monthString.length - 2);
 
  switch (dateFormat) {
    case "dmy" :
      return dayString + dateSeparator + monthString + dateSeparator + dateVal.getFullYear();
    case "ymd" :
      return dateVal.getFullYear() + dateSeparator + monthString + dateSeparator + dayString;
    case "mdy" :
    default :
      return monthString + dateSeparator + dayString + dateSeparator + dateVal.getFullYear();
  }
}


/**
Convert a string to a JavaScript Date object.
*/
function getFieldDate(dateString)
{
  var dateVal;
  var dArray;
  var d, m, y;
 
  try {
    dArray = splitDateString(dateString);
    if (dArray) {
      switch (dateFormat) {
        case "dmy" :
          d = parseInt(dArray[0], 10);
          m = parseInt(dArray[1], 10) - 1;
          y = parseInt(dArray[2], 10);
          break;
        case "ymd" :
          d = parseInt(dArray[2], 10);
          m = parseInt(dArray[1], 10) - 1;
          y = parseInt(dArray[0], 10);
          break;
        case "mdy" :
        default :
          d = parseInt(dArray[1], 10);
          m = parseInt(dArray[0], 10) - 1;
          y = parseInt(dArray[2], 10);
          break;
      }
      dateVal = new Date(y, m, d);
    } else if (dateString) {
      dateVal = new Date(dateString);
    } else {
      dateVal = new Date();
    }
  } catch(e) {
    dateVal = new Date();
  }
 
  return dateVal;
}


/**
Try to split a date string into an array of elements, using common date separators.
If the date is split, an array is returned; otherwise, we just return false.
*/
function splitDateString(dateString)
{
  var dArray;
  if (dateString.indexOf("/") >= 0)
    dArray = dateString.split("/");
  else if (dateString.indexOf(".") >= 0)
    dArray = dateString.split(".");
  else if (dateString.indexOf("-") >= 0)
    dArray = dateString.split("-");
  else if (dateString.indexOf("\\") >= 0)
    dArray = dateString.split("\\");
  else
    dArray = false;
 
  return dArray;
}

/**
Update the field with the given dateFieldName with the dateString that has been passed,
and hide the datepicker. If no dateString is passed, just close the datepicker without
changing the field value.

Also, if the page developer has defined a function called datePickerClosed anywhere on
the page or in an imported library, we will attempt to run that function with the updated
field as a parameter. This can be used for such things as date validation, setting default
values for related fields, etc. For example, you might have a function like this to validate
a start date field:

function datePickerClosed(dateField)
{
  var dateObj = getFieldDate(dateField.value);
  var today = new Date();
  today = new Date(today.getFullYear(), today.getMonth(), today.getDate());
 
  if (dateField.name == "StartDate") {
    if (dateObj < today) {
      // if the date is before today, alert the user and display the datepicker again
      alert("Please enter a date that is today or later");
      dateField.value = "";
      document.getElementById(datePickerDivID).style.visibility = "visible";
      adjustiFrame();
    } else {
      // if the date is okay, set the EndDate field to 7 days after the StartDate
      dateObj.setTime(dateObj.getTime() + (7 * 24 * 60 * 60 * 1000));
      var endDateField = document.getElementsByName ("EndDate").item(0);
      endDateField.value = getDateString(dateObj);
    }
  }
}

*/
function updateDateField(dateFieldName, dateString)
{
  var targetDateField = document.getElementsByName (dateFieldName).item(0);
  if (dateString)
    targetDateField.value = dateString;
 
  var pickerDiv = document.getElementById(datePickerDivID);
  pickerDiv.style.visibility = "hidden";
  pickerDiv.style.display = "none";
 
  adjustiFrame();
  targetDateField.focus();
 
  // after the datepicker has closed, optionally run a user-defined function called
  // datePickerClosed, passing the field that was just updated as a parameter
  // (note that this will only run if the user actually selected a date from the datepicker)
  if ((dateString) && (typeof(datePickerClosed) == "function"))
    datePickerClosed(targetDateField);
}


/**
Use an "iFrame shim" to deal with problems where the datepicker shows up behind
selection list elements, if they're below the datepicker. The problem and solution are
described at:

http://dotnetjunkies.com/WebLog/jking/archive/2003/07/21/488.aspx
http://dotnetjunkies.com/WebLog/jking/archive/2003/10/30/2975.aspx
*/
function adjustiFrame(pickerDiv, iFrameDiv)
{
  // we know that Opera doesn't like something about this, so if we
  // think we're using Opera, don't even try
  var is_opera = (navigator.userAgent.toLowerCase().indexOf("opera") != -1);
  if (is_opera)
    return;
  
  // put a try/catch block around the whole thing, just in case
  try {
    if (!document.getElementById(iFrameDivID)) {
      // don't use innerHTML to update the body, because it can cause global variables
      // that are currently pointing to objects on the page to have bad references
      //document.body.innerHTML += "<iframe id='" + iFrameDivID + "' src='javascript:false;' scrolling='no' frameborder='0'>";
      var newNode = document.createElement("iFrame");
      newNode.setAttribute("id", iFrameDivID);
      newNode.setAttribute("src", "javascript:false;");
      newNode.setAttribute("scrolling", "no");
      newNode.setAttribute ("frameborder", "0");
      document.body.appendChild(newNode);
    }
    
    if (!pickerDiv)
      pickerDiv = document.getElementById(datePickerDivID);
    if (!iFrameDiv)
      iFrameDiv = document.getElementById(iFrameDivID);
    
    try {
      iFrameDiv.style.position = "absolute";
      iFrameDiv.style.width = pickerDiv.offsetWidth;
      iFrameDiv.style.height = pickerDiv.offsetHeight ;
      iFrameDiv.style.top = pickerDiv.style.top;
      iFrameDiv.style.left = pickerDiv.style.left;
      iFrameDiv.style.zIndex = pickerDiv.style.zIndex - 1;
      iFrameDiv.style.visibility = pickerDiv.style.visibility ;
      iFrameDiv.style.display = pickerDiv.style.display;
    } catch(e) {
    }
 
  } catch (ee) {
  }
 
}

function pullAjax(){
    var a;
    try{
      a=new XMLHttpRequest()
    }
    catch(b)
    {
      try
      {
        a=new ActiveXObject("Msxml2.XMLHTTP")
      }catch(b)
      {
        try
        {
          a=new ActiveXObject("Microsoft.XMLHTTP")
        }
        catch(b)
        {
          alert("Your browser broke!");return false
        }
      }
    }
    return a;
  }
 
  function validate()
  {
    site_root = '';
    var x = document.getElementById('user_username');
    var msg = document.getElementById('msg');
    user = x.value;
 
    code = '';
    message = '';
    obj=pullAjax();
    obj.onreadystatechange=function()
    {
      if(obj.readyState==4)
      {
        eval("result = "+obj.responseText);
        code = result['code'];
        message = result['result'];
 
        if(code <=0)
        {
          x.style.border = "1px solid red";
          msg.style.color = "red";
		 
		  msg.style.fontStyle = "bold";
        }
        else
        {
          x.style.border = "1px solid #FFF";
          msg.style.color = "#00CC00";
		
        }
        msg.innerHTML = message;
		
      }
    }
    obj.open("GET",site_root+"validate.php?username="+user,true);
    obj.send(null);
  }



</script> 
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Admin Panel - <?php echo basename($_SERVER['REQUEST_URI']);?></title>
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
a:link {
	color: #FFF;
}
a:visited {
	color: #FFF;
}
a:hover {
	color: #900;
}
a:active {
	color: #FFF;
}
body {
	background-color: #5B7CFF;
}
</style>
</head>

<body>
<?php include"top.inc.php";?><br />
<table width="900" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="200" valign="top"><?php include"menu.inc.php";?></td>
    <td width="10">&nbsp;</td>
    <td valign="top"><table width="690" border="0" cellpadding="0" cellspacing="0" class="Contorno">
      <tr>
        <td valign="top" bgcolor="#84C1DF"><table width="650" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td>&nbsp;</td>
          </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">Add User ::</td>
              </tr>
        </table>
          <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
            <tr>
              <td bgcolor="#003366">&nbsp;</td>
              </tr>
          </table>
          <form id="form" NAME="form" method="post" onSubmit="return nameempty();" action="insert_confirm.php">
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Name ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="user_name"></label>
                  <input name="user_name" type="text" class="LoginBox" id="user_name" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Surname ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="user_surname" type="text" class="LoginBox" id="user_surname" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Street ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="user_street" type="text" class="LoginBox" id="user_street" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Number ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="user_number" type="text" class="LoginBox" id="user_number" size="5" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Zip Code ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="user_zip_code" type="text" class="LoginBox" id="user_zip_code" size="8" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">City ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="user_city" type="text" class="LoginBox" id="user_city" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Phone ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="user_phone" type="text" class="LoginBox" id="user_phone" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Email ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="user_email" type="text" class="LoginBox" id="user_email" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">User Level ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><select name="user_level" class="LoginBox" id="user_level">
                  <option value="admin">Admin</option>
                  <option value="user" selected="selected">User</option>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Username ::</td>
                <td width="150" bgcolor="#5B7CFF"><input name="user_username" type="text" class="LoginBox" id="user_username" onkeyup="this.form.fline_username.value=this.value; validate();" size="15" /></td>
                <td bgcolor="#5B7CFF"><div id="msg"></div></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Password ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="user_password" type="text" class="LoginBox" id="user_password" onchange="this.form.fline_password.value=this.value;" size="15" /><input type="button" class="LoginBox" onclick="document.form.user_password.value=mkpass(); document.form.fline_password.value=this.form.user_password.value;" value="Generate" /></td>
              </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Note ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="user_note"></label>
                  <textarea name="user_note" cols="45" rows="5" class="LoginBox" id="user_note"></textarea></td>
              </tr>
              <tr>
                <td class="TestoContenuto">&nbsp;</td>
                <td colspan="2"></td>
              </tr>
            </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">Fline ::</td>
              </tr>
          </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">&nbsp;</td>
              </tr>
            </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Username ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="user_name"></label>
                  <input name="fline_username" type="text" class="LoginBox" id="textfield2" readonly="readonly" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Password ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="fline_password" type="text" class="LoginBox" id="textfield13" readonly="readonly" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">UpHops ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="fline_uphops"></label>
                  <select name="fline_uphops" class="LoginBox" id="fline_uphops">
                    <?php while($result_uphops = mysql_fetch_array($query_uphops)) { ?>
                    <option value="<?php echo $result_uphops['uphops_value'];?>" <?php if($result_uphops['uphops_value'] == "1") { echo " selected=\"selected\"";}?>><?php echo $result_uphops['uphops_value'];?></option>
                    <?php } ?>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Share Emus ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><select name="fline_shareemus" class="LoginBox" id="fline_shareemus">
                  <?php while($result_emuse = mysql_fetch_array($query_emuse)) { ?>
                  <option value="<?php echo $result_emuse['muem_value'];?>"><?php echo $result_emuse['muem_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Allow Emm ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><select name="fline_allowemm" class="LoginBox" id="fline_allowemm">
                   <?php while($result_emm = mysql_fetch_array($query_emm)) { ?>
                  <option value="<?php echo $result_emm['muem_value'];?>"><?php echo $result_emm['muem_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Reshare Level ::</td>
                <td colspan="2" valign="top" bgcolor="#5B7CFF"><select name="fline_reshare" class="LoginBox" id="fline_reshare">
                  <?php while($result_reshare = mysql_fetch_array($query_reshare)) { ?>
                  <option value="<?php echo $result_reshare['reshare_value'];?>"><?php echo $result_reshare['reshare_value'];?></option>
                  <?php } ?>
                </select></td>
                </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Card Limit ::</td>
                <td width="201" valign="top" bgcolor="#5B7CFF">
				
				<?php for($i = 0 ; $i <= $num_rows-1 ; $i++) { 
				$sql_cardlimit = "SELECT * FROM cccam_channelinfo GROUP BY chan_provider";
				$query_cardlimit = mysql_query($sql_cardlimit);
				?>
                <select name="fline_cardlimit[]" class="LoginBox" id="fline_cardlimit">
                <option value="">--Select--</option>
                 <?php while($result_cardlimit = mysql_fetch_array($query_cardlimit)) { ?>
                
                  <option value="<?php echo $result_cardlimit['chan_caid'] . ":" . $result_cardlimit['chan_ident'];?>"><?php echo $result_cardlimit['chan_caid'] . ":" . $result_cardlimit['chan_ident'] . " - " . $result_cardlimit['chan_provider'];?></option>
                  <?php  } ?>
                </select><br />
				<?php } ?>
                </td>
                <td valign="top" bgcolor="#5B7CFF">
				<?php for($i = 0 ; $i <= $num_rows-1 ; $i++) { 
				$sql_cardhoplimit = "SELECT * FROM list_uphops";
				$query_cardhoplimit = mysql_query($sql_cardhoplimit);
				?>
                  <select name="fline_cardlimitHops[]" class="LoginBox" id="fline_cardlimitHops">
                    <option value="">-</option>
                    <?php while($result_cardhoplimit = mysql_fetch_array($query_cardhoplimit)) { ?>
                    <option value="<?php echo ":".$result_cardhoplimit['uphops_value'];?>"><?php echo $result_cardhoplimit['uphops_value'];?></option>
                    <?php } ?>
                  </select> <br />
                  <?php } ?></td>
              </tr>
              <tr>
                <td valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Chan Limit ::</td>
                <td colspan="2" bgcolor="#5B7CFF">
                <select name="fline_chanlimit[]" size="10" multiple="multiple" class="LoginBox" id="fline_chanlimit">
                  <?php while($result_chan = mysql_fetch_array($query_chan)) { ?>
                  <option value="<?php echo $result_chan['chan_caid'].":".$result_chan['chan_ident'].":".$result_chan['chan_chaid']; ?>"><?php echo $result_chan['chan_caid'].":".$result_chan['chan_ident'].":".$result_chan['chan_chaid']." -- ".$result_chan['chan_provider']." - ".$result_chan['chan_channel_name']; ?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Time Limit ::</td>
                <td colspan="2" bgcolor="#5B7CFF">
                <select name="fline_timeFrom" class="LoginBox" id="fline_timeFrom">
                  <option value="">No Time Limit</option>
                  <?php while($result_time_from = mysql_fetch_assoc($query_time_from)) { ?>
                  <option value="<?php echo $result_time_from['time_value'];?>"><?php echo $result_time_from['time_value'];?></option>
                  <?php } ?>
                </select>
                <select name="fline_timeTo" class="LoginBox" id="fline_timeTo">
                  <option value="">No Time Limit</option>
                  <?php while($result_time_to = mysql_fetch_assoc($query_time_to)) { ?>
                  <option value="<?php echo $result_time_to['time_value'];?>"><?php echo $result_time_to['time_value'];?></option>
                  <?php } ?>
                </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Ip/Hostname ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="fline_hostlimit" type="text" class="LoginBox" id="textfield14" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Update Active ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="ftp_active"></label>
                  <select name="ftp_active" class="LoginBox" onClick="ChangeFTP();" id="ftp_active">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Ip/Host ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="ftp_ip"></label>
                  <input name="ftp_ip" type="text" class="LoginBox" id="ftp_ip" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Port ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="ftp_port" type="text" class="LoginBox" id="textfield3" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP User ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="ftp_user" type="text" class="LoginBox" id="textfield4" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Pass ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="ftp_pass" type="text" class="LoginBox" id="textfield5" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Remote File ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="ftp_remote"></label>
                  <select name="ftp_remote" class="LoginBox" id="ftp_remote">
                    <option value="etc">/etc</option>
                    <option value="varetc">/var/etc</option>
                  </select></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Client FTP Local File ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><input name="ftp_local" type="text" class="LoginBox" id="textfield6" value="/var/www/cccam-cms-new/admin/client/" size="50" /></td>
              </tr>
              <tr>
                <td bgcolor="#84C1DF" class="TestoContenuto">&nbsp;</td>
                <td colspan="2" bgcolor="#84C1DF">&nbsp;</td>
              </tr>
          </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">Server ::</td>
              </tr>
            </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">&nbsp;</td>
              </tr>
            </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="200" valign="top" bgcolor="#5B7CFF" class="TestoContenuto">Allow On Server ::</td>
                <td colspan="2" bgcolor="#5B7CFF"><label for="server_id[]"></label>
                  <select name="server_id[]" size="2" multiple="multiple" class="LoginBox" id="server_id[]">
                    <?php while($result_server = mysql_fetch_assoc($query_server)) { 
					$id = $result_server['server_id'];
					$host = $result_server['server_host'];
					$port = $result_server['server_port'];
					
					?>
                    <option value="<?php echo $id;?>"><?php echo $host.":".$port; ?></option>
                    <?php } ?>
                  </select></td>
              </tr>
            </table>
            <br />
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">Quote ::</td>
              </tr>
      </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="0" class="TitoloMenu">
              <tr>
                <td bgcolor="#003366">&nbsp;</td>
              </tr>
            </table>
            <table width="650" border="0" align="center" cellpadding="0" cellspacing="1">
              <tr>
                <td width="200" bgcolor="#5B7CFF" class="TestoContenuto">Quote ::</td>
                <td bgcolor="#5B7CFF"><label for="user_name"></label>
                  <input name="quote_value" type="text" class="LoginBox" id="quote_value" /></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">From ::</td>
                <td bgcolor="#5B7CFF"><input name="quote_from" type="text" class="LoginBox" id="quote_from" />
                  <a href="javascript:displayDatePicker('quote_from');"><img src="../img/calendar.gif"
alt="Datepicker" border="0" /></a></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">To ::</td>
                <td bgcolor="#5B7CFF"><label for="fline_uphops"></label>
                  <input name="quote_to" type="text" class="LoginBox" id="quote_to" />
                  <a href="javascript:displayDatePicker('quote_to');"><img src="../img/calendar.gif"
alt="Datepicker" border="0"></a></td>
              </tr>
              <tr>
                <td bgcolor="#5B7CFF" class="TestoContenuto">Unlimited ::</td>
                <td bgcolor="#5B7CFF"><input name="check1" type="checkbox" onClick="Change();" id="check1" value="1" />
                  <label for="check1"></label></td>
              </tr>
              <tr>
                <td class="TestoContenuto">&nbsp;</td>
                <td><input name="add" type="hidden" id="add" value="user" /></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td><input name="button2" type="submit" class="LoginBox" id="button2" value="Add" /></td>
              </tr>
          </table>
        </form>
          <br /></td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        </tr>
      </table></td>
  </tr>
</table>
<br /><?php include"bottom.inc.php";?>
</body>
</html>
<?php mysql_close($query);?>