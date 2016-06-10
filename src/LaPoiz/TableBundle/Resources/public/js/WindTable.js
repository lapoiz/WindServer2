//Constantes
const HEURE_BEGIN_DAY = 7;
const HEURE_END_DAY = 21;

/**
 *
 * @constructor
 */
function WindTable(idName) {
    this.idName=idName;
    this.table=document.getElementById(this.idName);
    this.arrayData=[]; // On va mettre les data brut pour les exploiter ensuite
    this.arrayData["maree"]=[];

    /* Create THead */
    this.createTableHead();

    this.tbody=document.createElement("tbody");
    this.table.appendChild(this.tbody);


    //var loadingImgareURL="{{ asset('bundles/lapoizwind/images/loading.gif') }}";// UTILISER CETTE IMAGE POUR LE TITRE ET AUTRE DONN2ES QUI SE CHARGE
}

WindTable.prototype.createTableHead = function() {
    // cellule sur 2 ligne: Site
    this.thead=document.createElement("thead");
    this.table.appendChild(this.thead);

    var newRow = document.createElement("tr");
    var newCol = document.createElement("th");
    newCol.setAttribute("rowSpan", "2");
    var newTxt = document.createTextNode("Site");

    newCol.appendChild(newTxt);
    newRow.appendChild(newCol);
    this.thead.appendChild(newRow);

    var day=moment();
    day.startOf('day').fromNow();

    // ligne des dates
    for (numDay=0; numDay<10; numDay++) {
        this.newTitleDate(day.format("dddd DD"), newRow, numDay);
        day.add(1,'days');
    }

    // ligne des heures des dates
    var newRowDateHoure = document.createElement("tr");
    this.thead.appendChild(newRowDateHoure);
    for (day=0; day<10; day++) {
        this.newTitleDateHoure(newRowDateHoure, day);
    }

}


function intToHoure(intNum) {
    if (intNum<10) {
        return "0"+intNum;
    } else {
        return ""+intNum;
    }
}

/**
 * @param webSiteName: nom du site de prevision
 * @returns la ligne créée.
 */
WindTable.prototype.newWebSiteLine = function(webSiteName) {
    var newRowSiteName = document.createElement("tr");
    var newColSiteName = document.createElement("th");
    newColSiteName.setAttribute("rowSpan", "2");
    var newTxtSiteName = document.createTextNode(webSiteName);

    newColSiteName.appendChild(newTxtSiteName);
    newRowSiteName.appendChild(newColSiteName);
    this.tbody.appendChild(newRowSiteName);

    return newRowSiteName;
}

WindTable.prototype.newTitleDate = function(date, row, numDay) {
    var newColDate = document.createElement("th");
    newColDate.setAttribute("colSpan", (HEURE_END_DAY-HEURE_BEGIN_DAY+1));
    if (numDay%2 == 0 ) {
        newColDate.setAttribute("class", "titleDatePair");
    } else {
        newColDate.setAttribute("class", "titleDateImpair");
    }
    var newTxtDate = document.createTextNode(date);
    newColDate.appendChild(newTxtDate);
    row.appendChild(newColDate);
}

WindTable.prototype.newTitleDateHoure = function(row, numDay) {
    for (houre=HEURE_BEGIN_DAY;houre<=HEURE_END_DAY;houre++) {
        var newColHoure = document.createElement("th");
        var newTxtHoure = document.createTextNode(intToHoure(houre)+ "h");

        newColHoure.appendChild(newTxtHoure);

        if (numDay%2 == 0 ) {
            newColHoure.setAttribute("class", "titleDatePair");
        } else {
            newColHoure.setAttribute("class", "titleDateImpair");
        }

        row.appendChild(newColHoure);
    }
}

WindTable.prototype.addPrevision = function(previsionDate, prevision, row, arrayDataSite) {
    /*
     Dispo :
         previsionDate.date;// "22-05-2016"
         prevision.heure;   // 16
         prevision.wind;    // 20

     Eventuellement dispo:
         prevision.orientation
         prevision.meteo
         prevision.precipitation
         prevision.temperature
     */
    var previousPrevisionNumTd=row.childNodes.length; // first : nom du site internet de prévision

    var thisPrevisionNumTd=this.calculateNumTd(previsionDate.date,prevision.heure);
    //alert("prevision.date:"+previsionDate.date+"  prevision.heure:"+prevision.heure+"   numTd:"+thisPrevisionNumTd);

    if (thisPrevisionNumTd>0) {

        arrayDataSite[thisPrevisionNumTd] = [];
        var arrayDataSiteElem = arrayDataSite[thisPrevisionNumTd];
        this.putPrevisionOnArrayData(previsionDate, prevision, arrayDataSiteElem);

        if (thisPrevisionNumTd > 0) {
            // à ajouter, car dans le future
            var previousPrevisionWind = 0;
            if (previousPrevisionNumTd > 1) {
                // il y a une prevision avant => on récupére le wind
                previousPrevisionWind = parseInt(row.lastChild.innerText);
            } else {
                previousPrevisionWind = prevision.wind;
            }

            this.completePrevision(row, previousPrevisionNumTd, previousPrevisionWind, thisPrevisionNumTd, prevision.wind);

        }
    }
}

/**
 *  Calcul le numTd en fonction de la date et de l'heure
 * @param date du type "22-05-2016"
 * @param houre du type 16
 * @returns le numero de la td (nb de col)
 */
WindTable.prototype.calculateNumTd = function(date, houre) {
    if (this.isDisplayThisHoure(houre)) {
        // check the date
        var day = moment();
        day.startOf('day').fromNow();

        var numDate = -1;
        //alert("(day.format(\"DD-MM-YYYY\") === date) => ("+day.format("DD-MM-YYYY")+" === "+date+") =>"+((day.format("DD-MM-YYYY") === date)));
        for (numDay = 0; numDay < 10; numDay++) {
            if (day.format("DD-MM-YYYY") === date) {
                numDate = numDay;
            }
            day.add(1, 'days');
        }

        if (numDate >= 0) {
            console.log("calculateNumTd("+date+", "+houre+") numDate:"+numDate+" => "+(numDate * (HEURE_END_DAY-HEURE_BEGIN_DAY) + parseInt(houre)-HEURE_BEGIN_DAY+1));
            // check the houre
            return numDate * (HEURE_END_DAY-HEURE_BEGIN_DAY) + parseInt(houre) -HEURE_BEGIN_DAY +1; // +1 => 1er TD pour le nom du site Internet de prevision
        } else {
            return -1;
        }
    } else {
        return -1;
    }
}

WindTable.prototype.completePrevision = function(row,previousPrevisionNumTd,previousPrevisionWind,thisPrevisionNumTd,thisPrevisionWind) {
    //console.log("completePrevision(previousPrevisionNumTd:"+previousPrevisionNumTd+", previousPrevisionWind:"+previousPrevisionWind+
    //    ", thisPrevisionNumTd:"+thisPrevisionNumTd+", thisPrevisionWind:"+thisPrevisionWind+")");
    var nbTd=thisPrevisionNumTd-previousPrevisionNumTd+1;
    var windValuetoAddPerTd = (parseFloat(thisPrevisionWind)-parseFloat(previousPrevisionWind))/nbTd;

    for (numTd=1;numTd<nbTd;numTd++) {
        var newColPrev = document.createElement("td");
        var wind = Math.round((previousPrevisionWind+(numTd*windValuetoAddPerTd)));
        //console.log("wind=Math.round(("+previousPrevisionWind+"+("+numTd+"*"+windValuetoAddPerTd+")))="+wind);
        var newTxtPrev = document.createTextNode(wind);
        //var newTxtPrev = document.createTextNode(previousPrevisionNumTd+numTd-1);

        newColPrev.appendChild(newTxtPrev);
        //newColPrev.setAttribute("class", this.getClassWind(wind));
        newColPrev.setAttribute("style", "background-color:"+this.getCellColorWind(wind)+"; color:"+this.getTxtColorWind(wind));
        row.appendChild(newColPrev);
    }
    newColPrev = document.createElement("td");
    newTxtPrev = document.createTextNode(thisPrevisionWind);

    //newColPrev.setAttribute("class", this.getClassWind(thisPrevisionWind));
    newColPrev.setAttribute("style", "background-color:"+this.getCellColorWind(thisPrevisionWind)+"; color:"+this.getTxtColorWind(thisPrevisionWind));
    newColPrev.appendChild(newTxtPrev);
    row.appendChild(newColPrev);
}


WindTable.prototype.putPrevisionOnArrayData = function(previsionDate, prevision, arrayDataSiteElem) {
    arrayDataSiteElem["wind"]=prevision.wind;
    arrayDataSiteElem["date"]=previsionDate.date; // jamais utilisé, mais util pour test
    arrayDataSiteElem["heure"]=prevision.heure; // jamais utilisé, mais util pour test

    if (!isEmpty(prevision.orientation) && !isEmpty(prevision.orientation.deg) && prevision.orientation.deg>=0) {
        arrayDataSiteElem["orientation"]=prevision.orientation.deg;
    }

    if (!isEmpty(prevision.meteo)) {
        arrayDataSiteElem["meteo"]=prevision.meteo;
    }

    if (!isEmpty(prevision.precipitation)) {
        arrayDataSiteElem["precipitation"]=prevision.precipitation;
    }

    if (!isEmpty(prevision.temperature)) {
        arrayDataSiteElem["temperature"]=prevision.temperature;
    }
}

/**
 * Ajoute à this.table une ligne contenant les orientations si présente, sinon vide ...
 * arrayDataSite contient toutes les données:
 *  arrayDataSite[previsionNumTd][wind/date/heure/orientation/meteo/precipitation/temperature]
 *  avec les éléments facultatif: orientation/meteo/precipitation/temperature
 *
 * @param arrayDataSite
 */
WindTable.prototype.putLineOrientation = function(arrayDataSite) {
    var row=document.createElement("tr");
    row.setAttribute("class","orientationLine")
    this.tbody.appendChild(row);

    var precNumTd = 2;
    var precOrientation=0;

    for (var numTd in arrayDataSite) {
        //alert("numTd:"+numTd+"   date:"+arrayDataSite[numTd]["date"]+"   heure:"+arrayDataSite[numTd]["heure"]+"   orientation:"+arrayDataSite[numTd]["orientation"]);
        if (arrayDataSite[numTd].hasOwnProperty("orientation")) {
            // orientation are on array
            var thisOrientation=parseFloat(arrayDataSite[numTd]["orientation"]);// en deg
            var nbTd=numTd-precNumTd;
            for (var intNumId=1;intNumId<nbTd;intNumId++) {
                this.addTdEmpty(row);
            }
            this.addTdOrientation(thisOrientation,row);
            precNumTd=numTd;
            precOrientation=thisOrientation;
        }
    }
}

WindTable.prototype.addTdEmpty = function(row) {
    var newTd = document.createElement("td");
    row.appendChild(newTd);
}

WindTable.prototype.addTdOrientation = function(orientation, row) {
    var xmlns = "http://www.w3.org/2000/svg";

    var newTdOrientation = document.createElement("td");
    newSpanOrient=document.createElement("span");
    newTdOrientation.appendChild(newSpanOrient);

    var newSVG = document.createElementNS(xmlns, "svg");
    newSVG.setAttributeNS(null,"viewBox","0 0 100 100");
    newSVG.setAttributeNS(null,"class","flecheOrientationGraph"+orientationVentSpot[this.getOrientationTableKey(orientation)]);
    newSVG.setAttributeNS(null,"version","1.1");
    newSpanOrient.appendChild(newSVG);

    newTransformOrient=document.createElementNS(xmlns,"g");
    newTransformOrient.setAttributeNS(null,"transform","rotate("+orientation+",50,50) translate(0,5)");
    newSVG.appendChild(newTransformOrient);

    newPath=document.createElementNS(xmlns,"path");
    newPath.setAttributeNS(null,"d","m50,0 -20,30 16,-3 -3,63 14,0 -3,-63 16,3 -20,-30z");
    newPath.setAttributeNS(null,"stroke-width","0");
    newTransformOrient.appendChild(newPath);

    row.appendChild(newTdOrientation);
}

/**
 * En fonction de la puissance du vent, retourne la couleur de la cellule
 * @param wind: vent en noeud
 */
WindTable.prototype.getCellColorWind = function(wind) {
    if (wind<10) {
        return "RGBA(255,255,0,"+(Math.round(0.5*wind)/10)+")"; // jaune
    } else if  (wind>20) {
        return "RGBA(0,0,255,1)"; // rouge
    } else {
        var rgba="RGBA(0,0,255,1)";
        // Entre 10 et 20
        switch (wind) {
            case 10:
                rgba="RGBA(255,255,0,0.6)";
                break;
            case 11:
                rgba="RGBA(255,215,0,0.7)";
                break;
            case 12:
                rgba="RGBA(152,205,50,0.7)";
                break;
            case 13:
                rgba="RGBA(0,128,0,0.5)";
                break;
            case 14:
                rgba="RGBA(0,128,0,0.7)";
                break;
            case 15:
                rgba="RGBA(0,128,0,1)";
                break;
            case 16:
                rgba="RGBA(0,100,0,0.9)";
                break;
            case 17:
                rgba="RGBA(0,100,0,1)";
                break;
            case 18:
                rgba="RGBA(0,0,255,0.6)";
                break;
            case 19:
                rgba="RGBA(0,0,255,0.8)";
                break;
            case 20:
                rgba="RGBA(0,0,255,1)";
                break;
        }
        return rgba;
    }
}

/**
 * En fonction de la puissance du vent, retourne la couleur du texte
 * @param wind: vent en noeud
 */
WindTable.prototype.getTxtColorWind = function(wind) {
    if (wind<15) {
        return "black";
    } else {
        return "white";
    }
}


// *********************
// Restriction Maree
// *********************

WindTable.prototype.putRestrictionMaree = function(date, heureBegin, heureEnd, etatRestriction) {
    if (isEmpty(this.arrayData["maree"][date])) {
        this.arrayData["maree"][date]=[];
    }
    heureBegin=this.roundHeureMaree(heureBegin);
    heureEnd=this.roundHeureMaree(heureEnd);
    this.arrayData["maree"][date][heureBegin]={heureEnd:heureEnd, etatRestriction:etatRestriction};
}

/**
 * Ajoute une ligne pour les marée à partir de arrayData
 */
WindTable.prototype.createLineMaree = function() {
    var newRowMaree = document.createElement("tr");
    this.tbody.appendChild(newRowMaree);

    var newCol = document.createElement("th");
    var newTxt = document.createTextNode("Marée");
    newCol.appendChild(newTxt);
    newRowMaree.appendChild(newCol);

    var firstCel=true;
    var numHeurePrec=0;
    var colSpan=0;

    for (var date in this.arrayData["maree"]) {
        firstCel=true;
        console.log("date: "+date);
        for (var heureBegin in this.arrayData["maree"][date]) {

            if (firstCel) {
                this.addTdMaree(newRowMaree, heureBegin,"No");
                firstCel=false;
            }
            this.addTdMaree(newRowMaree, (this.arrayData["maree"][date][heureBegin].heureEnd-heureBegin),this.arrayData["maree"][date][heureBegin].etatRestriction);

            numHeurePrec=this.arrayData["maree"][date][heureBegin].heureEnd;
        }
        if (this.arrayData["maree"][date][heureBegin].heureEnd<24) {
            this.addTdMaree(newRowMaree, (24-this.arrayData["maree"][date][heureBegin].heureEnd),"No");
        }
    }
}


WindTable.prototype.addTdMaree = function(row,colSpan,etatRestriction) {
    console.log("addTdMaree(row,"+colSpan+","+etatRestriction+")");
    var newColMaree = document.createElement("td");
    var newTxtMaree = document.createTextNode(" ");

    newColMaree.appendChild(newTxtMaree);
    newColMaree.setAttribute("class", this.getMareeClass(etatRestriction));
    newColMaree.setAttribute("colSpan", colSpan);

    row.appendChild(newColMaree);
}

/**
 * Retourne l'heure ronde
 * @param houre: 8.4 => 8
 */
WindTable.prototype.roundHeureMaree = function(houre) {
    return Math.round(houre);
}

/**
 * Return la classe de la cellule (TD) en fonction de etatRestriction
 * @param etatRestriction: OK/war/KO
 */
WindTable.prototype.getMareeClass = function(etatRestriction) {
    if (etatRestriction==="OK") {
        return "restrictionOK";
    } else if (etatRestriction==="KO") {
        return "restrictionKO";
    } else if (etatRestriction==="warn") {
        return "restrictionWarn";
    } else {
        return "restrictionNo";
    }
}

/**
 * Retourne la key du tableau des orientation
 * @param orientation: du type 45 => return "45.0"
 */
WindTable.prototype.getOrientationTableKey = function(orientation) {
    var orientTxt=orientation.toString();
    var cherche=orientTxt.indexOf(".");
    return orientTxt+ (cherche>-1?"":".0");
}

isEmpty = function(variable) {
    return variable === undefined || variable === null || variable === '' || variable.length === 0;
}

WindTable.prototype.isDisplayThisHoure = function(houre) {
    return houre>=HEURE_BEGIN_DAY & houre<=HEURE_END_DAY;
}

