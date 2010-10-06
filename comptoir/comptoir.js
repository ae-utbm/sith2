idNumberPre = "nbProd";
idPricePre = "priceProd";

var total;
var currentBalance;
var newProducts = new Array();
/*
function addProduct(barCode) {
	var found = false;
	var i = newProducts.length - 1;

	while (!found && i>0)
	{
		if (newProducts[i] == "-"+barCode)
		{
			newProducts[i]="";
			found = true;
		}
	}

	if (!found)
	{
		newProducts[] = barCode;
	}
}

function removeProduct(barCode) {
	var found = false;
	var i = newProducts.length - 1;

	while (!found && i>0)
	{
		if (newProducts[i] == barCode)
		{
			newProducts[i]="";
			found = true;
		}
	}

	if (!found)
	{
		newProducts[] = "-"+barCode;
	}
}
*/
function getTotal() {
	if (typeof(total)=='undefined')
	{
		total = Math.round(parseFloat(document.getElementById('priceTotal').firstChild.nodeValue.replace(',', '.'))*100);
	}
	return total;
}

function addTotal(price) {
	if (typeof(total)=='undefined')
	{
		total = Math.round(parseFloat(document.getElementById('priceTotal').firstChild.nodeValue.replace(',', '.'))*100);
	}
	
	total+=price;

	if (total<0)
	{
		total=0;
	}

	return total;
}

function removeTotal(price) {
	return addTotal(-price);
}

function getCurrentBalance() {
	if (typeof(currentBalance)=='undefined')
	{
		currentBalance = Math.round(parseFloat(document.getElementById('soldeCourant').firstChild.nodeValue.replace(',', '.'))*100);
	}
	return currentBalance;
}

//currentCart = new Cart();

function multiClass(eltId) {
	list = document.getElementById('productsTabs');
	arrLinkId = new Array('typeProd1','typeProd2','typeProd3','typeProd4','typeProd6','typeProd7','typeProd8','typeProd12','typeProd27');
	intNbLinkElt = new Number(arrLinkId.length);
	strContent = new String();
	for (i=0; i<intNbLinkElt; i++) {
		strContent = arrLinkId[i]+"Contents";
		if ( arrLinkId[i] == eltId ) {
			document.getElementById(arrLinkId[i]).className = "typeProdTab current";
			document.getElementById(strContent).className = 'products';
		} else {
			document.getElementById(arrLinkId[i]).className = "typeProdTab";
			document.getElementById(strContent).className = 'products hide';
		}
	}

	return false;
}

/*
function Cart() { 
	var total;
	var currentBalance;
	var cart = new Array();

	this.addProduct = function(barCode, name, price) {
		if (isBalanceEnough(price))
		{
			total += price;
		}
		else
		{
			alert('Solde insuffisant');
		}
	}

	var isBalanceEnough = function(price) { 
        	return ((total+price) <= currentBalance); 
	}

	var getTotal = function() {
		if (total == undefined)
		{
			total = Math.round(parseFloat(document.getElementById('priceTotal').firstChild.nodeValue.replace(',', '.'))*100);
		}
		return total;
	}

	var getCurrentBalance = function() {
		if (currentBalance == undefined)
		{
			currentBalance = Math.round(parseFloat(document.getElementById('soldeCourant').firstChild.nodeValue.replace(',', '.'))*100);
		}
		return currentBalance;
	}
}
*/

function increase(code_barre, price)
{
	if (isProductCanBeAdded(price))
	{
		tdNumber = document.getElementById(idNumberPre+code_barre);
		tdPrice = document.getElementById(idPricePre+code_barre);

		newValue = Math.round(parseFloat(tdPrice.firstChild.nodeValue.replace(',', '.'))*100+price);
        
		tdNumber.firstChild.nodeValue=parseInt(tdNumber.firstChild.nodeValue)+1;
		tdPrice.firstChild.nodeValue=newValue/100 + " \u20AC";

		addToNewProductsFields(code_barre);

		increaseTotal(price);
	}
	else
	{
		alert('Solde insuffisant');
	}
	return false;
}

function decrease(code_barre, price)
{
	tdNumber = document.getElementById(idNumberPre+code_barre);
	tdPrice = document.getElementById(idPricePre+code_barre);

	nbValue = parseInt(tdNumber.firstChild.nodeValue);
	oldPrice = parseFloat(tdPrice.firstChild.nodeValue.replace(',', '.'));

	if (nbValue>0)
	{
		tdNumber.firstChild.nodeValue = nbValue-1;

		newPrice = Math.round(oldPrice*100-price);
		tdPrice.firstChild.nodeValue=newPrice/100 + " \u20AC";

		decreaseTotal(price);
	}
	else
	{
		tdNumber.firstChild.nodeValue = 0;
		tdPrice.firstChild.nodeValue = 0 + " \u20AC";
	}

	addToNewProductsFields("-"+code_barre);

	return false;
}

function increaseTotal(price)
{
	addTotal(price);

	tdTotalPrice = document.getElementById("priceTotal");

	tdTotalPrice.firstChild.nodeValue = getTotal()/100 + " \u20AC";

	return false;
}

function decreaseTotal(price)
{
	removeTotal(price);

	tdTotalPrice = document.getElementById("priceTotal");

	tdTotalPrice.firstChild.nodeValue = getTotal()/100 + " \u20AC";

	return false;
}

function addToCart(code_barre, nom, prix)
{
	if (isProductCanBeAdded(prix))
	{
		if (!document.getElementById('prod'+code_barre))
		{
			addProductRow(code_barre, nom, prix);
			addToNewProductsFields(code_barre);
		}
		else
		{
			increase (code_barre, prix);
		}
	}
	else
	{
		alert('Solde insuffisant');
	}

	return false;
}

function addProductRow(code_barre, nom, prix)
{
	var table = document.getElementById("panier");

	var newRow;

	if (document.getElementById("total"))
	{
		newRow = panier.insertRow(document.getElementById("total").rowIndex);
	}
	else
	{
		newRow = panier.insertRow(-1);
	}

	newRow.id = "prod"+code_barre;

	var newCell = newRow.insertCell(-1);
	newCell.innerHTML = "<a onclick=\"return decrease('"+code_barre+"', "+prix+");\" href=\"#\">-</a>";

	newCell = newRow.insertCell(-1);
	newCell.id = "nbProd"+code_barre;
	newCell.innerHTML = "1";

	newCell = newRow.insertCell(-1);
	newCell.innerHTML = "<a onclick=\"return increase('"+code_barre+"', "+prix+");\" href=\"#\">+</a>";

	newCell = newRow.insertCell(-1);
	newCell.innerHTML = nom;

	newCell = newRow.insertCell(-1);
	newCell.id = "priceProd"+code_barre;
	newCell.innerHTML = prix/100+" \u20AC";

	increaseTotal(prix);
}

function checkBarCodeInput()
{
	var inputCodeBarre = document.getElementById("code_barre");

	if (inputCodeBarre.value)
	{
		var arrayNouveauxProduitsHidden = document.getElementsByName('nouveaux_produits');

		for (var i=0; i<arrayNouveauxProduitsHidden.length; i++)
		{
			arrayNouveauxProduitsHidden[i].value += inputCodeBarre.value;
		}
	}

	return true;
}

function addToNewProductsFields(barCode)
{
	var arrayNouveauxProduitsHidden = document.getElementsByName('nouveaux_produits');

	for (var i=0; i<arrayNouveauxProduitsHidden.length; i++)
	{
		arrayNouveauxProduitsHidden[i].value += barCode+";";
	}

	return true;
}

function isProductCanBeAdded(price)
{
	return ((getTotal()+price)<=getCurrentBalance());
}

