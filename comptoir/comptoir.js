idNumberPre = "nbProd";
idPidPricePrepriceProd";
idTabsList = "productsTabs";
idPlatProd = "platProd";

var total;
var currentBalance;
var newProducts = new Array();
var tabsId = new Array();

function getTabsId()
{
	if (tabsId.length==0)
	{
		ulTabs = document.getElementById(idTabsList);

		for (i=0;i<ulTabs.childNodes.length;i++)
		{
			tabsId.push(ulTabs.childNodes.item(i).firstChild.id);
		}
	}

	return tabsId;
}

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

function changeActiveTab(eltId) {
	var arrLinkId = getTabsId();
	var strContent = new String();
	for (i=0; i<arrLinkId.length; i++) {
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

function increase(code_barre, price, plateau)
{
	tdNumber = document.getElementById(idNumberPre+code_barre);

  tmpP = (parseInt(tdNumber.firstChild.nodeValue) + 1) % 6 == 0 && plateau;

	if (isProductCanBeAdded(price) || tmpP)
	{
		tdPrice = document.getElementById(idPricePre+code_barre);
		tdNumber.firstChild.nodeValue=parseInt(tdNumber.firstChild.nodeValue)+1;

    if (!tmpP) {
		  newValue = Math.round(parseFloat(tdPrice.firstChild.nodeValue.replace(',', '.'))*100+price);
		  tdPrice.firstChild.nodeValue=newValue/100 + " \u20AC";

		  increaseTotal(price);
    }

    if (plateau) {
      if (parseInt(tdNumber.firstChild.nodeValue) >= 6)
        document.getElementById(idPlatProd+code_barre).innerHTML = 'P';
    }

		addToNewProductsFields(code_barre);
	}
	else
	{
		alert('Solde insuffisant');
	}
	return false;
}

function decrease(code_barre, price, plateau)
{
	tdNumber = document.getElementById(idNumberPre+code_barre);
	tdPrice = document.getElementById(idPricePre+code_barre);

	nbValue = parseInt(tdNumber.firstChild.nodeValue);
	oldPrice = parseFloat(tdPrice.firstChild.nodeValue.replace(',', '.'));

	if (nbValue>0)
	{
		tdNumber.firstChild.nodeValue = nbValue-1;

    if (!plateau || nbValue % 6 != 0) {
		  newPrice = Math.round(oldPrice*100-price);
		  tdPrice.firstChild.nodeValue=newPrice/100 + " \u20AC";

		  decreaseTotal(price);
    }

    if (plateau) {
      if (parseInt(tdNumber.firstChild.nodeValue) < 6)
        document.getElementById(idPlatProd+code_barre).innerHTML = '';
    }
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

function addToCart(code_barre, nom, prix, plateau)
{
	if (isProductCanBeAdded(prix))
	{
		if (!document.getElementById('prod'+code_barre))
		{
			addProductRow(code_barre, nom, prix, plateau);
			addToNewProductsFields(code_barre);
		}
		else
		{
			increase (code_barre, prix, plateau);
		}
	}
	else
	{
		alert('Solde insuffisant');
	}

	return false;
}

function addProductRow(code_barre, nom, prix, plateau)
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
	newCell.innerHTML = "<a onclick=\"return decrease('"+code_barre+"', "+prix+", "+plateau+");\" href=\"#\">-</a>";

	newCell = newRow.insertCell(-1);
	newCell.id = idNumberPre+code_barre;
	newCell.innerHTML = "1";

	newCell = newRow.insertCell(-1);
	newCell.innerHTML = "<a onclick=\"return increase('"+code_barre+"', "+prix+", "+plateau+");\" href=\"#\">+</a>";

	newCell = newRow.insertCell(-1);
	newCell.innerHTML = nom;

  newCell = newRow.insertCell(-1);
  newCell.id = idPlatProd+code_barre;
  newCell.innerHTML = '';

	newCell = newRow.insertCell(-1);
	newCell.id = idPricePre+code_barre;
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
