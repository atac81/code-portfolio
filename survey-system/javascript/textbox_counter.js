/*
 * DHTML textbox character counter script. Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
 * Revised by Brian Matsinger for online survey system project
 */

maxL=1500; // max number of characters allowed
var bName = navigator.appName;
function taLimit(taObj) {
	if (taObj.value.length >= maxL) {
		// Reached the Maximum length so trim the textarea
		taObj.value = taObj.value.substring(0, maxL);
	}
}

function taCount(taObj,Cnt) { 
	objCnt=createObject(Cnt);
	objVal=taObj.value;
	if (objVal.length>maxL) objVal=objVal.substring(0,maxL);
	if (objCnt) {
		if(bName == "Netscape"){	
			objCnt.textContent=maxL-objVal.length;}
		else{objCnt.innerText=maxL-objVal.length;}
	}
}

function createObject(objId) {
	if (document.getElementById) return document.getElementById(objId);
	else if (document.layers) return eval("document." + objId);
	else if (document.all) return eval("document.all." + objId);
	else return eval("document." + objId);
}

// Second iteration (for use in forms with multiple character limits)

maxL2=1500; // max number of characters allowed
function taLimit2(taObj) {
	if (taObj.value.length >= maxL2) {
		// Reached the Maximum length so trim the textarea
		taObj.value = taObj.value.substring(0, maxL2);
	}
}

function taCount2(taObj,Cnt) { 
	objCnt=createObject2(Cnt);
	objVal=taObj.value;
	if (objVal.length>maxL2) objVal=objVal.substring(0,maxL2);
	if (objCnt) {
		if(bName == "Netscape"){	
			objCnt.textContent=maxL2-objVal.length;}
		else{objCnt.innerText=maxL2-objVal.length;}
	}
}

function createObject2(objId) {
	if (document.getElementById) return document.getElementById(objId);
	else if (document.layers) return eval("document." + objId);
	else if (document.all) return eval("document.all." + objId);
	else return eval("document." + objId);
}

// Third iteration (for use in forms with multiple character limits)

maxL3=1500; // max number of characters allowed
function taLimit3(taObj) {
	if (taObj.value.length >= maxL3) {
		// Reached the Maximum length so trim the textarea
		taObj.value = taObj.value.substring(0, maxL3);
	}
}

function taCount3(taObj,Cnt) { 
	objCnt=createObject3(Cnt);
	objVal=taObj.value;
	if (objVal.length>maxL3) objVal=objVal.substring(0,maxL3);
	if (objCnt) {
		if(bName == "Netscape"){	
			objCnt.textContent=maxL3-objVal.length;}
		else{objCnt.innerText=maxL3-objVal.length;}
	}
}

function createObject3(objId) {
	if (document.getElementById) return document.getElementById(objId);
	else if (document.layers) return eval("document." + objId);
	else if (document.all) return eval("document.all." + objId);
	else return eval("document." + objId);
}

// Fourth iteration (for use in forms with multiple character limits)

maxL4=1500; // max number of characters allowed
function taLimit4(taObj) {
	if (taObj.value.length >= maxL4) {
		// Reached the Maximum length so trim the textarea
		taObj.value = taObj.value.substring(0, maxL4);
	}
}

function taCount4(taObj,Cnt) { 
	objCnt=createObject4(Cnt);
	objVal=taObj.value;
	if (objVal.length>maxL4) objVal=objVal.substring(0,maxL4);
	if (objCnt) {
		if(bName == "Netscape"){	
			objCnt.textContent=maxL4-objVal.length;}
		else{objCnt.innerText=maxL4-objVal.length;}
	}
}

function createObject4(objId) {
	if (document.getElementById) return document.getElementById(objId);
	else if (document.layers) return eval("document." + objId);
	else if (document.all) return eval("document.all." + objId);
	else return eval("document." + objId);
}

// Fifth iteration (for use in forms with multiple character limits)

maxL5=1500; // max number of characters allowed
function taLimit5(taObj) {
	if (taObj.value.length >= maxL5) {
		// Reached the Maximum length so trim the textarea
		taObj.value = taObj.value.substring(0, maxL5);
	}
}

function taCount5(taObj,Cnt) { 
	objCnt=createObject5(Cnt);
	objVal=taObj.value;
	if (objVal.length>maxL5) objVal=objVal.substring(0,maxL5);
	if (objCnt) {
		if(bName == "Netscape"){	
			objCnt.textContent=maxL5-objVal.length;}
		else{objCnt.innerText=maxL5-objVal.length;}
	}
}

function createObject5(objId) {
	if (document.getElementById) return document.getElementById(objId);
	else if (document.layers) return eval("document." + objId);
	else if (document.all) return eval("document.all." + objId);
	else return eval("document." + objId);
}
