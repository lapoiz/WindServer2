const SvgLegendWidth= 200;
const SvgLegendHeight= 200;

/**
 *
 * @constructor
 */
function LegendGraph(idName) {
    Graph.call(this, idName);

    var viewBoxParam = "0 0 "+SvgLegendWidth+" "+SvgLegendHeight;
    this.svg.setAttributeNS(null,'viewBox',viewBoxParam);
    this.svg.setAttributeNS(null,'preserveAspectRatio',"xMidYMid meet");

    this.drawCadre();
}

LegendGraph.prototype = Object.create(Graph.prototype);

LegendGraph.prototype.drawCadre = function() {
    this.newRectSVGElement(0,0, '100%', '100%','cadreLegend');
}

/**
 * Put the list of website name
 * @param listeSites: JSON elem with liste of Site wich each eleme have site.nom
 */
LegendGraph.prototype.addWebSite = function(listeSites) {
    for (numSite = 0; numSite < listeSites.length; numSite++) {
        var site=listeSites[numSite];
        var newGroupLegSite=this.newGroupSVGElement(10,(numSite*20),'100%','20px','rectSiteLegend',"legend_site_"+site.nom);
        this.newTextSVGElement(10,((numSite+1)*20),site.nom,'siteLegend',newGroupLegSite);
        this.newTextSVGElement(100,((numSite+1)*20),site.date,'siteUpdateLegend',newGroupLegSite);
    }
}