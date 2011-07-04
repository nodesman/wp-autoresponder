<?php
include "wp-load.php";


if (!current_user_can('level_8'))
{
	exit;
}

global $wpdb;
	$nid = $_GET['nid'];
	?>
	<html>
	<head>
	<title>Manage Recipients</title>
	<style>
	.unseen {
		display:none;
	}
	.set {
		padding: 10px;
		border: 1px solid #999;
		font-family:Verdana, Geneva, sans-serif;
		font-size:12px;
		margin-top:20px;
	}
	.clause {
		font-family: Verdana, Geneva, sans-serif;
		font-size: 12px;
		border: 1px solid #CCC;
		padding:10px;
	}
	body {
		font-family:Verdana, Geneva, sans-serif;
		font-size: 12px;
	}
	</style>
	</head>
	<body>
	<script>
	//enumerations
	function createSelect(theOptions,condnum,fieldnum)
	{
		sel = document.createElement("select");
		for (option in theOptions)
		{
			var opt = document.createElement("option");
			opt.setAttribute("name","info_"+condnum+"_"+fieldnum);
			opt.innerHTML = theOptions[option];
			sel.appendChild(opt);
		}
		return sel;
	}
	var enumFieldNames = new Array();
	var enumeratedFields = new Array();
	<?php
	$GetEnumsQuery = "select * from ".$wpdb->prefix."wpr_custom_fields where type='enum' and nid=$nid";
	$enumeratedFields = $wpdb->get_results($GetEnumsQuery);
	foreach ($enumeratedFields as $num=>$enumeratedField)
	{
		$enumerations = explode(",",$enumeratedField->enum);
		$array = implode("','",$enumerations);
		$array = "['$array']";
		?>
	enumFieldNames[<?php echo $num ?>] = '<?php echo $enumeratedField->name ?>';
	enumeratedFields['<?php echo $enumeratedField->name ?>'] = <?php echo $array; ?>;
		<?php
		
	}
	?>
	function replaceParams(string,setnum,fieldnum)
	{
		string = string.replace("condnum",setnum);
		string = string.replace("fieldnum",fieldnum);
		return string;
	}
	
	function changeFieldType(fieldname, setnum,fieldnum)
	{
		var fieldcontainer = document.getElementById('info_container_'+setnum+'_'+fieldnum);
		fieldcontainer.innerHTML='';
		var equalitycontainer = document.getElementById('equality_container_'+setnum+'_'+fieldnum);
		equalitycontainer.setAttribute("style","display: inline;");
		equalitycontainer.innerHTML = '';
		if (enumFieldNames.indexOf(fieldname) != -1)
		{
			
			//change the equality type..
			var eqtemp = document.getElementById('enum_equality').innerHTML;
			while (eqtemp.indexOf('condnum') != -1)
				eqtemp = eqtemp.replace("condnum",setnum);
			while (eqtemp.indexOf('fieldnum') != -1)
				eqtemp = eqtemp.replace("fieldnum",fieldnum);
			
			equalitycontainer.innerHTML = eqtemp;
			//change the field type..
			var theoptions = enumeratedFields[fieldname];
			var sel = createSelect(theoptions,setnum,fieldnum);
			var name = 'info_'+setnum+'_'+fieldnum;
			sel.setAttribute('id',name);
			sel.setAttribute('name',name);
			fieldcontainer.appendChild(sel);
		}
		else if (fieldname == 'dateofsubscription')
		{
			var input = document.createElement("input");
			input.setAttribute("type","text");
			input.setAttribute("size","20");
			input.setAttribute("name","info_"+setnum+"_"+fieldnum);
			input.setAttribute("id","info_"+setnum+"_"+fieldnum);
			fieldcontainer.appendChild(input);
			fieldcontainer.innerHTML += "(mm/dd/yyyy)";

			var eqtemp = document.getElementById('date_equality').innerHTML;
			while (eqtemp.indexOf('condnum') != -1)
			eqtemp = eqtemp.replace("condnum",setnum);
			while (eqtemp.indexOf('fieldnum') != -1)
			eqtemp = eqtemp.replace("fieldnum",fieldnum);
			equalitycontainer.innerHTML = eqtemp;
		}
		else
		{
			var eqtemp = document.getElementById('normal_equality').innerHTML;
			while (eqtemp.indexOf('condnum') != -1)
				eqtemp = eqtemp.replace("condnum",setnum);
			while (eqtemp.indexOf('fieldnum') != -1)
				eqtemp = eqtemp.replace("fieldnum",fieldnum);
			equalitycontainer.innerHTML = eqtemp;
			var input = document.createElement("input");
			input.setAttribute("type","text");
			input.setAttribute("size","20");
			input.setAttribute("name","info_"+setnum+"_"+fieldnum);
			input.setAttribute("id","info_"+setnum+"_"+fieldnum);
			fieldcontainer.appendChild(input);
		}	
	}
	function hideAddLink(setnum,fieldnum)
	{
		var ele = document.getElementById('add_'+setnum+'_'+fieldnum);
		ele.setAttribute("style","display:none");
	}

	function isdefined( variable)
	{
		return (typeof(window[variable]) == "undefined")?  false: true;
	}
	
	
	</script>
	<h2>Select Recipients</h2>
	<br />
	Define some sets of subscribers by filling in the blanks in the following sentences. <br />
	<br />
    <strong style="color: #F00">Note: If some subscribers are common to any of these sets, those subscribers will not receive more than one e-mail. These sets are combined (UNION).</strong><br>
<br>

	Send this email to:<br />
	<br />
	<div id="normal_equality" class="unseen">
		<select name="equality_condnum_fieldnum" id="equality_condnum_fieldnum">
		  <option value="equal">Is Equal To</option>
		  <option value="notequal">Is Not Equal To</option>
          <option value="notnull">Is Not Empty</option>
		  <option value="lessthan">Is Less Than</option>
		  <option value="greaterthan">Is Greater Than</option>
		  <option value="startswith">That Starts With</option>
		  <option value="contains">That Contains (substring)</option>
		  <option value="endswith">That Ends With</option>
		  <option value="rlike">That Satisfies regular expression (RLIKE) </option>
		</select>
	</div>
	<div id="enum_equality" class="unseen">
		<select name="equality_condnum_fieldnum" id="equality_condnum_fieldnum">
		  <option value="equal">Is Equal To</option>
		  <option value="notequal">Is Not Equal To</option>
		</select>
	</div>
    <div id="date_equality" class="unseen">
    <select name="equality_condnum_fieldnum" id="equality_condnum_fieldnum">
       <option value="after">After</option>
       <option value="before">Before</option>
    </select>
    </div>
	<div class="unseen" id="fieldtemplate">
	  <div id="set_condnum_fieldnum" class="clause">
		<!--conjunction-->
		<select name="custom_fields_condnum_fieldnum" id="custom_fields_condnum_fieldnum" onChange="changeFieldType(this.options[this.selectedIndex].value,condnum,fieldnum)">
		  <?php $query = "select * from ".$wpdb->prefix."wpr_custom_fields where nid=$nid";
		  $cfields = $wpdb->get_results($query);
		  foreach ($cfields as $cfield)
		  {
	?>
	<option value="<?php echo $cfield->name ?>"><?php echo $cfield->label ?> (<?php echo $cfield->name ?>) </option>
	<?php  
		  }
		  ?>
  		  <option value="dateofsubscription">Date Of Subscribing</option>
		</select>

		<div id="equality_container_condnum_fieldnum" style="display:inline;">
		<select name="equality_condnum_fieldnum" id="equality_condnum_fieldnum">
		  <option value="equal">Is Equal To</option>
		  <option value="notequal">Is Not Equal To</option>
          <option value="notnull">Is Not Empty</option>
		  <option value="lessthan">Is Less Than</option>
		  <option value="greaterthan">Is Greater Than</option>
		  <option value="startswith">That Starts With</option>
		  <option value="contains">That Contains (substring)</option>
		  <option value="endswith">That Ends With</option>
		  <option value="rlike">That Satisfies regular expression (RLIKE) </option>
		</select>
		</div>
		<div id="info_container_condnum_fieldnum" style="display: inline">
		  <input type="text" id="info_condnum_fieldnum" name="info_condnum_fieldnum" />
		</div> <!--removelink-->	<a href="javascript:;" onClick="addField(condnum,nextfield);hideAddLink(condnum,fieldnum);" id="add_condnum_fieldnum"> Add Condition.. </a> </div>
	</div>
	<div class="unseen">
<div id="removetempl"><a style="display: inline" href="javascript:removeCondition(condnum,fieldnum);">Remove</a></div>
	  <div id="andor">
		<select name="conjunction_condnum_fieldnum" id="conjunction_condnum_fieldnum" >
		  <option>AND</option>
		  <option>OR</option>
		</select>
	  </div>
	</div>
	<div id="sets"> </div>

	<input type="button" onClick="javascript:addSet();" value="Add Set">
    <br>
<br>
<br>
	<input type="button" value="Save Recipients" onClick="formSetQueries();">
	<script>
	var setCount=0;
	var nid = <?php echo $nid ?>;
	var setList = new Array();
	var sets = document.getElementById('sets');
	var andor = document.getElementById('andor');
	
	function addSet()
	{
		var conditions = new Array();
		setList[setCount] = conditions;
		//now the html
		var newset = document.createElement("div");
		var heading = document.createElement("h2");
		heading.innerHTML='Set '+setCount+':';
		newset.appendChild(heading);
				var remb = document.createElement("input");
		remb.setAttribute("type","button");
		remb.setAttribute("value","Remove Set");
		remb.setAttribute("onclick",'removeSet('+setCount+')');
		remb.setAttribute("style","float:right");
		newset.appendChild(remb);

		var prevBut = document.createElement('input');
		prevBut.setAttribute('type','button');
		prevBut.setAttribute('value','Show The Recipients In This Set');
		prevBut.setAttribute('onclick','previewSet('+setCount+')');
		prevBut.setAttribute("style","float:right");
		newset.appendChild(prevBut);

		var thep = document.createElement('p');
		thep.innerHTML = 'All subscribers who have';
		newset.appendChild(thep);
		newset.setAttribute("id","set_"+setCount);
		newset.setAttribute("class","set");
		sets.appendChild(newset);
		addField(setCount,1);
		setCount++;	
	}
	
	
	
	function addField(setCount,fieldNum)
	{
		var template = document.getElementById('fieldtemplate').innerHTML;
		var theHTML = template;
		if (fieldNum !=1) //add the add/or selector
		{
			theHTML = theHTML.replace("<!--conjunction-->",andor.innerHTML);
		    theHTML = theHTML.replace("<!--removelink-->",replaceParams(document.getElementById('removetempl').innerHTML,setCount,fieldNum));
		}
		
		while (theHTML.indexOf("condnum") != -1)
			theHTML = theHTML.replace("condnum",setCount);
		while (theHTML.indexOf("fieldnum") != -1)
			theHTML = theHTML.replace("fieldnum",fieldNum);
		theHTML = theHTML.replace("nextfield",fieldNum+1);
		
		var cont = document.createElement("div");
		cont.innerHTML = theHTML;
		//add element in the corresponding setList element.
		var exists=fieldNum;
		setList[setCount][fieldNum] = exists;
		var set = document.getElementById('set_'+setCount);
		set.appendChild(cont);
		document.getElementById('custom_fields_'+setCount+'_'+fieldNum).onchange();
	}
	
	function removeCondition(setnum,fieldnum)
	{
		var ele = document.getElementById('set_'+setnum+'_'+fieldnum);
		var thepar = ele.parentNode;
		thepar.removeChild(ele);
		//remove field from array;

		
		//now make the add field available when removing the last field 
		//on the second last field.
		
		//is this the last field?
		var maxIndex=0;
		
		for (var index in setList[setnum])
		{
			if (index > maxIndex)
			  maxIndex = index;
		}
		//maxIndex is the last field.
		
		//is ths the last field?		
		if (setList[setnum][maxIndex] == fieldnum)
		{
			var thenextitem = setList[setnum][maxIndex-1];
			var addlink = document.getElementById('add_'+setnum+'_'+thenextitem);
			addlink.setAttribute('style','display:inline');

		}
		setList[setnum].splice(fieldnum,1);
	}
	
	function removeSet(num)
	{
		var setc = document.getElementById('set_'+num);
		var thepar = setc.parentNode;
		thepar.removeChild(setc);
		setList.splice(num-1,1);
	}
		
	function getFieldName(setnum,fieldnum)
	{
		var name = 'custom_fields_'+setnum+'_'+fieldnum;
		return document.getElementById(name);
	}
	
	function getEquality(setnum,fieldnum)
	{
		var name = 'equality_'+setnum+'_'+fieldnum;
		return document.getElementById(name)
	}
	
	function getConditionValue(setnum,fieldnum)
	{
		var name = 'info_'+setnum+'_'+fieldnum;
		return document.getElementById(name);
	}
	
	function getConditionConjunction(setnum,fieldnum)
	{
		var name = 'conjunction_'+setnum+'_'+fieldnum;
		return document.getElementById(name);
	}
	
	function getConditionQuery(setnum,condnum)
	{
		if (condnum != 1)
		{
			var conjfield = getConditionConjunction(setnum,condnum);
			var conjunction = conjfield.options[conjfield.selectedIndex].value;
		}
		//field name
		var cffield = getFieldName(setnum,condnum);
		var cf = cffield.options[cffield.selectedIndex].value;
		//equality
		var equalityField = getEquality(setnum,condnum);
		var eq = equalityField.options[equalityField.selectedIndex].value;

		var valueField = getConditionValue(setnum,condnum);
	

		if (valueField.tagName == 'INPUT')
		{
			value = valueField.value;
		}
		else if (valueField.tagName == 'SELECT')
		{
		   value = valueField.options[valueField.selectedIndex].value;
		}
		
		if (eq.indexOf("notnull") != -1)
		{
			value = "nothing";
		}
		if (value =='')
		{
			alert('You have not filled the value for a condition. Click OK to enter the value');
			valueField.focus();
			return false;
		}
		
		if (typeof(conjunction) != 'undefined')
			var conditionString = ' '+conjunction+' '+cf+' '+eq+' '+value;
		else
			var conditionString = cf+' '+eq+' '+value;
		return conditionString;
	}
	
	function formQuery(setnum)
	{
		var conditions = setList[setnum];
		var thequery='';
		for (var condition in conditions)
		{		
		    theConditionQuery = getConditionQuery(setnum,condition)
			if (!theConditionQuery)
			   return false;
			thequery += theConditionQuery;
			
			if (!thequery)
			{
				return false;
			}
		}			
		return thequery; 
	}
	function previewSet(setnum)
	{
		thequery = formQuery(setnum);
		if (!thequery)
		{
			 return false;
		}
		thestring = encodeBase64(thequery);
		window.open('<?php bloginfo('home')?>/?wpr-admin-action=view_recipients&string='+thestring+'&nid='+nid,'recipients','width=600,height=600');
	}
	
	function formSetQueries()
	{
		if (setList.length == 0)
		{
			saveQuery(""); //save an empty query. which means send to everyone on the list.
		}
		var finalQuery = new Array();
		for (var i in setList)
		{
			thequery = formQuery(i);
			if (thequery == "false")
			{
				break;
				return false;
			}
			finalQuery[i] = thequery;
		}
		var querytoreturn = finalQuery.join("%set%");
		saveQuery(querytoreturn);
		window.close();
		
	}
	function saveQuery(querytosave)
	{
		var thefield = window.opener.document.getElementById('recipients');
		if (thefield)
		{
			thefield.setAttribute("value",querytosave);
			window.opener.showSavedSet();
		}
		else
		   window.close();
	}



var END_OF_INPUT = -1;
	var base64Chars = new Array(
		'A','B','C','D','E','F','G','H',
		'I','J','K','L','M','N','O','P',
		'Q','R','S','T','U','V','W','X',
		'Y','Z','a','b','c','d','e','f',
		'g','h','i','j','k','l','m','n',
		'o','p','q','r','s','t','u','v',
		'w','x','y','z','0','1','2','3',
		'4','5','6','7','8','9','+','/'
	);
	 
	var reverseBase64Chars = new Array();
	for (var i=0; i < base64Chars.length; i++){
		reverseBase64Chars[base64Chars[i]] = i;
	}
	 
	var base64Str;
	var base64Count;
	
	function setBase64Str(str){
		base64Str = str;
		base64Count = 0;
	}
	function readBase64(){    
		if (!base64Str) return END_OF_INPUT;
		if (base64Count >= base64Str.length) return END_OF_INPUT;
		var c = base64Str.charCodeAt(base64Count) & 0xff;
		base64Count++;
		return c;
	}
	function encodeBase64(str){
		setBase64Str(str);
		var result = '';
		var inBuffer = new Array(3);
		var lineCount = 0;
		var done = false;
		while (!done && (inBuffer[0] = readBase64()) != END_OF_INPUT){
			inBuffer[1] = readBase64();
			inBuffer[2] = readBase64();
			result += (base64Chars[ inBuffer[0] >> 2 ]);
			if (inBuffer[1] != END_OF_INPUT){
				result += (base64Chars [(( inBuffer[0] << 4 ) & 0x30) | (inBuffer[1] >> 4) ]);
				if (inBuffer[2] != END_OF_INPUT){
					result += (base64Chars [((inBuffer[1] << 2) & 0x3c) | (inBuffer[2] >> 6) ]);
					result += (base64Chars [inBuffer[2] & 0x3F]);
				} else {
					result += (base64Chars [((inBuffer[1] << 2) & 0x3c)]);
					result += ('=');
					done = true;
				}
			} else {
				result += (base64Chars [(( inBuffer[0] << 4 ) & 0x30)]);
				result += ('=');
				result += ('=');
				done = true;
			}
			lineCount += 4;
			if (lineCount >= 76){
				result += ('\n');
				lineCount = 0;
			}
		}
		return result;
	}
	function readReverseBase64(){   
		if (!base64Str) return END_OF_INPUT;
		while (true){      
			if (base64Count >= base64Str.length) return END_OF_INPUT;
			var nextCharacter = base64Str.charAt(base64Count);
			base64Count++;
			if (reverseBase64Chars[nextCharacter]){
				return reverseBase64Chars[nextCharacter];
			}
			if (nextCharacter == 'A') return 0;
		}
		return END_OF_INPUT;
	}
	 
	function ntos(n){
		n=n.toString(16);
		if (n.length == 1) n="0"+n;
		n="%"+n;
		return unescape(n);
	}
	 
	function decodeBase64(str){
		setBase64Str(str);
		var result = "";
		var inBuffer = new Array(4);
		var done = false;
		while (!done && (inBuffer[0] = readReverseBase64()) != END_OF_INPUT
			&& (inBuffer[1] = readReverseBase64()) != END_OF_INPUT){
			inBuffer[2] = readReverseBase64();
			inBuffer[3] = readReverseBase64();
			result += ntos((((inBuffer[0] << 2) & 0xff)| inBuffer[1] >> 4));
			if (inBuffer[2] != END_OF_INPUT){
				result +=  ntos((((inBuffer[1] << 4) & 0xff)| inBuffer[2] >> 2));
				if (inBuffer[3] != END_OF_INPUT){
					result +=  ntos((((inBuffer[2] << 6)  & 0xff) | inBuffer[3]));
				} else {
					done = true;
				}
			} else {
				done = true;
			}
		}
		return result;
	}
	
	var query = window.opener.document.getElementById('recipients').value;
	if (query !='')
	{
		restoreValues(query);
	}
	
	function restoreValues(query)
	{
		var sets = query.split('%set%');
		for (set in sets)
		{
			restoreSet(set,sets[set]);
		}
	}
	
	function restoreSet(num,query)
	{
		setCount = num;
		addSet();
		conditions = query.split(" ");
		//first condition
		var field = document.getElementById('custom_fields_'+num+'_1');
		field.value=conditions[0];
		field.onchange();
		
		var eq = document.getElementById('equality_'+num+'_1');
		eq.value=conditions[1];
		
		var valueF = document.getElementById('info_'+num+'_1');
		valueF.value=conditions[2];

		var currConditionNumber = 2;
		for (var i=3; i<conditions.length;i+=4)
		{
			suffix = '_'+num+'_'+currConditionNumber;
			addField(num,currConditionNumber);
			var conj = document.getElementById('conjunction'+suffix);
			conj.value = conditions[i];
			
			var field = document.getElementById('custom_fields'+suffix);
			field.value=conditions[i+1];
			field.onchange();
			
			var eq = document.getElementById('equality'+suffix);
			eq.value=conditions[i+2];

			var valueF = document.getElementById('info'+suffix);
			valueF.value=conditions[i+3];
			
			currConditionNumber++;
		}
		
	}
	
	
	</script>
	<div class="sets"> </div>
	</body>
	</html>

