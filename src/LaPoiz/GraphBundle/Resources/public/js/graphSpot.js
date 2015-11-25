
/* Initialise le Graph */
function initGraph() {
    /* init les svg */
    svgGraph=new WindGraph("graphSVG");
    legend=new Legend("legendHTML");
}

/** Use the JSon */
function putOnGraph(jSon) {

    /* Put websites on legend */
    legend.addWebSite(jSon.forecast);

    var listeSites=jSon.forecast;
    var today=moment().startOf('day');

    for (numSite = 0; numSite < listeSites.length; numSite++) {
        var site=listeSites[numSite];
        var spline=new Spline(svgGraph,site.nom);
        for (numPrevisionDate = 0; numPrevisionDate < site.previsions.length; numPrevisionDate++) {
            /*
           "previsions": [
             {
             "date": "02-08-2015",
             "previsions": [
             { "heure": "02",
             */
            var previsionDate=site.previsions[numPrevisionDate];


            for (numPrevision = 0; numPrevision < previsionDate.previsions.length; numPrevision++) {
                /*
                 "previsions": [
                 { "heure": "02",
                 "wind": 4 },
                 "orientation":{"deg":315,"nom":"nw"},
                 "precipitation":"0",
                 "meteo":"r-a" }
                 */
                var prevision=previsionDate.previsions[numPrevision];

                // Verifie si previsionDate n'est pas avant aujourd'hui
                var previsionDateJS=moment(previsionDate.date,"DD-MM-YYYY").startOf('day');
                var point;
                nbJour=previsionDateJS.diff(today,'days');
                if (nbJour>=0) {
                    var x=svgGraph.getXOnGraph(moment(previsionDate.date,"DD-MM-YYYY").startOf('day'), prevision.heure);
                    spline.addPoint(x, svgGraph.getYOnGraph(prevision.wind));

                    if (!isEmpty(prevision.orientation) && !isEmpty(prevision.orientation.deg) && prevision.orientation.deg>=0) {
                        svgGraph.drawFlecheOrientation(prevision.orientation.deg, x);
                    }

                    if (!isEmpty(prevision.meteo)) {
                        svgGraph.drawMeteoIcon(prevision.meteo, x);
                    }

                    if (!isEmpty(prevision.precipitation)) {
                        svgGraph.drawPrecipitation(prevision.precipitation, x);
                    }
                }
            }
        }
        spline.drawSpline();
    }
}

function displayRestrictionMaree() {
    if (svgGraph.mareeRestrictionGroup.getAttributeNS(null, 'visibility')=='hidden') {
        svgGraph.mareeRestrictionGroup.setAttributeNS(null, 'visibility', 'visible');
    }else {
        svgGraph.mareeRestrictionGroup.setAttributeNS(null, 'visibility', 'hidden');
    }
}

function showTooltip(evt) {
    //console.log("showTooltip");
    var point=evt.target;
    var x= point.getAttributeNS(null,'cx');
    var y= point.getAttributeNS(null,'cy');

    svgGraph.tooltip.setAttributeNS(null,"visibility","visible");
    svgGraph.tooltip_bg.setAttributeNS(null,"visibility","visible");

    svgGraph.setTooltipData(x,y);
    //console.log("showTooltip out");
}
function hideTooltip() {
    svgGraph.tooltip.setAttributeNS(null,"visibility","hidden");
    svgGraph.tooltip_bg.setAttributeNS(null,"visibility","hidden");
}

function displayBoutonMarree() {
    legend.displayBtnMaree();
}

/**
 * Charge les données des restrictions des maréees
 * @param listeDate
 */
function loadMareeOnGraph(listeDate) {
    // array de date du type "2015-08-13"
    var today=moment().startOf('day'); // Todo: verifier que la date est apr�s aujourd'hui

    svgGraph.mareeRestrictionGroup.setAttributeNS(null, 'visibility', 'hidden');

    for (numDate = 0; numDate < listeDate.length; numDate++) {
        var dateJS = moment(listeDate[numDate].date,"YYYY-MM-DD").startOf('day'); // "2015-08-13"

        if (!isEmpty(listeDate[numDate].restrictions)) {
            drawPlageRestrictionFromList(dateJS,listeDate[numDate].restrictions.OK, "OK");
            drawPlageRestrictionFromList(dateJS,listeDate[numDate].restrictions.warn, "warn");
            drawPlageRestrictionFromList(dateJS,listeDate[numDate].restrictions.KO, "KO");
        }
    }
}
/**
 * Dessine les plage de restriction sur la base de la liste des plages
 [
     {
       "begin": "08:00:00",
       "end": "08:55:37"
     },
     {
       "begin": "14:38:22",
       "end": "20:00:00"
     }
 ]
 */
function drawPlageRestrictionFromList(dateJS,listePlageRestriction,etatRestriction) {
    if (!isEmpty(listePlageRestriction)) {
        for (numRestriction = 0; numRestriction < listePlageRestriction.length; numRestriction++) {
            // heure au format 08:55:37 � transformer en un nombre 5.9
            svgGraph.drawRestrictionMaree(dateJS, convertiHeureStringEnNombre(listePlageRestriction[numRestriction].begin),
                convertiHeureStringEnNombre(listePlageRestriction[numRestriction].end), etatRestriction);
        }
    }
}
/**
 * @param heureString au format 08:30:37
 * @return nombre d'heure, dans l'exemple 8.5 (ne prend pas en compte les secondes - inutile dans notre cas)
 */
function convertiHeureStringEnNombre(heureString) {
    var date=moment(heureString,"HH:mm:ss");
    var startTime=moment().startOf('day'); // 0 heure
    var duration = moment.duration(date.diff(startTime));
    return duration.asHours();
}

