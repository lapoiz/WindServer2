
/** Use the JSon for prevision */
function putPrevOnTable(jSon) {

    var listeSites=jSon.forecast;
    var today=moment().startOf('day');

    for (numSite = 0; numSite < listeSites.length; numSite++) {
        var site=listeSites[numSite];

        var trWebSite=windTable.newWebSiteLine(site.nom);
        windTable.tbody.appendChild(trWebSite);
        windTable.arrayData[site.nom]=[];

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
                    /*
                        Dispo :
                            previsionDate.date;
                            prevision.heure;
                            prevision.wind;

                        Eventuellement dispo:
                            prevision.orientation
                            prevision.meteo
                            prevision.precipitation
                            prevision.temperature
                    */
                    windTable.addPrevision(previsionDate, prevision, trWebSite, windTable.arrayData[site.nom]);
                }
            }
        }

        windTable.putLineOrientation(windTable.arrayData[site.nom]);
    }
    $('#wait').html('');
}

/** Use the JSon for Marée */
function putMareeOnTable(listeDate) {
    // array de date du type "2015-08-13"
    var today=moment().startOf('day');

    for (numDate = 0; numDate < listeDate.length; numDate++) {
        var dateJS = moment(listeDate[numDate].date,"YYYY-MM-DD").startOf('day'); // "2015-08-13"

        if (!isEmpty(listeDate[numDate].restrictions)) {
            putPlageRestrictionFromList(listeDate[numDate].date,listeDate[numDate].restrictions.OK, "OK");
            putPlageRestrictionFromList(listeDate[numDate].date,listeDate[numDate].restrictions.warn, "warn");
            putPlageRestrictionFromList(listeDate[numDate].date,listeDate[numDate].restrictions.KO, "KO");
        }
    }
    windTable.createLineMaree();
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
function putPlageRestrictionFromList(date,listePlageRestriction,etatRestriction) {
    if (!isEmpty(listePlageRestriction)) {
        for (numRestriction = 0; numRestriction < listePlageRestriction.length; numRestriction++) {
            // heure au format 08:55:37 � transformer en un nombre 5.9
            windTable.putRestrictionMaree(date, convertiHeureStringEnNombre(listePlageRestriction[numRestriction].begin),
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