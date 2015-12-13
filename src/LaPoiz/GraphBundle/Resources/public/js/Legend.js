/**
 * @constructor
 */
function Legend(idName) {
    this.idName=idName;
}

/**
 * Put the list of website name
 * @param listeSites: JSON elem with liste of Site wich each eleme have site.nom
 */
Legend.prototype.addWebSite = function(listeSites) {
    var divList=$("#legend_listWebsite");
    divList.html('');

    for (numSite = 0; numSite < listeSites.length; numSite++) {
        var site=listeSites[numSite];
        divList.append('<div class="legend_label_'+site.nom+'"><span>'+site.nom+'</span> ' +
            '<span class="siteUpdateLegend">(derniere maj:'+site.lastUpdate+')</span></div>');
    }
}

Legend.prototype.displayBtnMaree = function() {
    var divBtn=$("#legend_btnActions");
    divBtn.html('');
    if (isMaree) {
        var btn = document.createElement('button');
        //var txt = document.createTextNode("R&eacute;strisction d&ucirc; aux mar&eacute;es");
        btn.innerHTML = "R&eacute;strisction d&ucirc; aux mar&eacute;es";

        btn.setAttribute('type', 'button');
        btn.setAttribute('class', 'btn btn-primary');
        btn.setAttribute('onclick', "displayRestrictionMaree()");
        divBtn.append(btn);
    }
}