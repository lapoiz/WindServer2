//Constantes
const SvgWidth= 800;
const SvgHeight= 300;
const MargeLeft= 50;
const MargeRight= 50;
const MargeBottom= 30;
const MargeTop= 10;
const NbJourPrev=10; /* pr�vision � nbJourPrev jours max */
const NdsMax=40;
const NdsForNavMin=12;
const NdsForNavGood=15;
const HoureBeginDay=8;
const HoureEndDay=20;
const HauteurFlecheOrient=200;
const LargeurFlecheOrient=50;
const YNdsPositionFleche=2.5;

/**
 *
 * @constructor
 */
function WindGraph(idName) {
    Graph.call(this, idName);

    /* Parametres generaux */
    this.xMax=SvgWidth-MargeLeft-MargeRight;
    this.yMax=SvgHeight-MargeBottom-MargeTop;
    this.xPerDay=this.xMax/NbJourPrev; /* largeur en x pour une journ�e */
    this.yPerNds=this.yMax/NdsMax; /* �quivalant 1 Nd -> yPerNds pixel */

    var viewBoxParam = "0 0 "+SvgWidth+" "+SvgHeight;
    this.svg.setAttributeNS(null,'viewBox',viewBoxParam);
    this.svg.setAttributeNS(null,'preserveAspectRatio',"xMidYMid meet");

    var loadingImgareURL="{{ asset('bundles/lapoizwind/images/loading.gif') }}";// UTILISER CETTE IMAGE POUR LE TITRE ET AUTRE DONN2ES QUI SE CHARGE
    this.tooltip=this.newTextSVGElement(0,0,"tooltip",null,null,"tooltip"); //id="tooltip"
    this.tooltip.setAttributeNS(null, "visibility","hidden");

    this.mareeRestrictionGroup=this.newGroupSVGElement(0,0,"mareeRestrictionGroup"); // En premier afin qu'il soit en dessous du graph

    this.drawFlechesAxes();
    this.drawAxes();
    this.drawDateOnX();
    this.drawNdsOnY();
    this.drawLimiNav();
    this.splineGroup=this.newGroupSVGElement();// ordre de profondeur
    this.pointsGroup=this.newGroupSVGElement();// ordre de profondeur
    this.tooltip=this.drawTooltip();
    this.drawFlecheOrientationDef("flecheOrientDef");
}

WindGraph.prototype = Object.create(Graph.prototype);

/* Coordonnées normales */
WindGraph.prototype.getXvalue = function(x) {
    return x+MargeLeft;
}
WindGraph.prototype.getYvalue = function(y) {
    return SvgHeight-MargeBottom-y;
}

/**
 * Retourne le X projet� si l'axe �tait comme � l'�cole (inverse de getXvalue())
 * @param x
 * @returns la veleur de x dans un system de coordonn�e classique
 */
WindGraph.prototype.getValueX = function(x) {
    return x-MargeLeft;
}
/**
 * Retourne le Y projet� si l'axe �tait comme � l'�cole (inverse de getYvalue())
 * @param y
 * @returns la veleur de y dans un system de coordonn�e classique
 */
WindGraph.prototype.getValueY = function(y) {
    return SvgHeight-MargeBottom-y;
}

WindGraph.prototype.drawFlechesAxes= function () {
    var marker=document.createElementNS(SvgNS,'marker');
    marker.setAttributeNS(null,'id','fleche');
    marker.setAttributeNS(null,'orient','auto');

    var poly=document.createElementNS(SvgNS,'polyline');
    poly.setAttributeNS(null,'points','0,0 6,2 0,4');
    marker.appendChild(poly);

    this.defsSvgGraph.appendChild(marker);
}

/* Dessine les axes des X et Y */
WindGraph.prototype.drawAxes = function() {
    var chemin="M "+this.getXvalue(0)+" "+this.getYvalue(this.yMax)+" L "+this.getXvalue(0)+" "+this.getYvalue(0)+
        "L "+this.getXvalue(this.xMax)+" "+this.getYvalue(0);
    this.newPathSVGElement(chemin,'axesGraph');
}

/* Dessine les dates sur l'axe X */
WindGraph.prototype.drawDateOnX = function() {
    /* Pr�vision sur les 10 prochains jours max */
    moment.locale('fr');
    var now = moment();
    now.add(-1, 'days'); /* Pour commencer � aujourd'hui dans la boucle */

    /* cr�er la defs pour la nuit */
    var linearGradient=this.newLinearGradientSVGElement("degradeNuit","0","0","100%","0%");
    this.addStopLinearGradientSVGElement(linearGradient,'stop1','0%');
    this.addStopLinearGradientSVGElement(linearGradient,'stop2','10%');
    this.addStopLinearGradientSVGElement(linearGradient,'stop3','90%');
    this.addStopLinearGradientSVGElement(linearGradient,'stop4','100%');

    var xCurrent=this.xPerDay/2;
    var jour;
    var xMidiRatio=0.1+(0.8*(12-HoureBeginDay))/(HoureEndDay-HoureBeginDay); // ratio pour l'emplacement de 12h entre 8h et 20h, pour avoir x il faut multiplier par xPerDay
    for (i = 0; i < NbJourPrev; i++) {
        now.add(1, 'days');
        jour=now.format("ddd D");

        /* Cr�er les nuits entre les dates */
        var rectNight=this.newRectSVGElement(this.getXvalue(i*this.xPerDay),this.getYvalue(this.yMax),this.xPerDay,
            this.yMax,'nuitGraph',null);
        rectNight.setAttributeNS(null,"fill","url(#degradeNuit)");

        /* Ecrit la date */
        this.newTextSVGElement(this.getXvalue(xCurrent),this.getYvalue(-20),jour,"dateAxesGraph");
        xCurrent+=this.xPerDay;

        /* Cr�er les lignes s�parant les jours */
        var path="M "+this.getXvalue((i+1)*this.xPerDay)+" "+this.getYvalue(-20)+" L "+this.getXvalue((i+1)*this.xPerDay)
            +" "+this.getYvalue(this.yMax);
        this.newPathSVGElement(path,"subAxesDateGraph");

        /* Cr�er les lignes de midi +�crire les heures 8h, 12h et 20h */

        path="M "+this.getXvalue((i+xMidiRatio)*this.xPerDay)+" "+this.getYvalue(0)+" L "+this.getXvalue((i+xMidiRatio)*this.xPerDay)
            +" "+this.getYvalue(this.yMax);
        this.newPathSVGElement(path,"subAxesDateMidiGraph");

        this.newTextSVGElement(this.getXvalue((i+0.1)*this.xPerDay),this.getYvalue(0)+7,HoureBeginDay+"h","heureAxesGraph");
        this.newTextSVGElement(this.getXvalue((i+xMidiRatio)*this.xPerDay),this.getYvalue(0)+7,"12h","heureAxesGraph");
        this.newTextSVGElement(this.getXvalue((i+0.9)*this.xPerDay),this.getYvalue(0)+7,HoureEndDay+"h","heureAxesGraph");

    }
}

/* Dessine les lignes et valeurs sur Y : Nds */
WindGraph.prototype.drawNdsOnY = function() {
    var nbEntreNdsPrincipaux=10;
    var nbNdsPrincipal = Math.ceil(NdsMax/nbEntreNdsPrincipaux);
    for (i = 1; i <= nbNdsPrincipal; i++) {
        if (i*nbEntreNdsPrincipaux<=NdsMax) {
            /* Nds principaux (10, 20 ...) */
            var path = "M " + this.getXvalue(-5) + " " + this.getYvalue(i*nbEntreNdsPrincipaux*this.yPerNds) + " L " +
                this.getXvalue(this.xMax) + " " + this.getYvalue(i*nbEntreNdsPrincipaux*this.yPerNds);
            this.newPathSVGElement(path, "subAxesNdsGraph");
            this.newTextSVGElement(this.getXvalue(-15),this.getYvalue(i*nbEntreNdsPrincipaux*this.yPerNds-4),
                i*nbEntreNdsPrincipaux,"ndsAxesGraph");
            this.newTextSVGElement(this.getXvalue(this.xMax)+12,this.getYvalue(i*nbEntreNdsPrincipaux*this.yPerNds-4),
                i*nbEntreNdsPrincipaux,"ndsAxesGraph");

            /* Nds secondaires (5, 15 ...) */
            path = "M " + this.getXvalue(0) + " " + this.getYvalue((i-0.5)*nbEntreNdsPrincipaux*this.yPerNds) + " L " +
                this.getXvalue(this.xMax) + " " + this.getYvalue((i-0.5)*nbEntreNdsPrincipaux*this.yPerNds);
            this.newPathSVGElement(path, "subSubAxesNdsGraph");
            this.newTextSVGElement(this.getXvalue(-10),this.getYvalue((i-0.5)*nbEntreNdsPrincipaux*this.yPerNds-2),
                (i-0.5)*nbEntreNdsPrincipaux,"ndsSubAxesGraph");
            this.newTextSVGElement(this.getXvalue(this.xMax)+8,this.getYvalue((i-0.5)*nbEntreNdsPrincipaux*this.yPerNds-2),
                (i-0.5)*nbEntreNdsPrincipaux,"ndsSubAxesGraph");
        } // else : ca ne tombe pas pile -> on n'affiche pas la valeur haute au dessus de NdsMax
    }
}

WindGraph.prototype.getYOnGraph = function(nds) {
    return this.getYvalue(nds*this.yPerNds);
}

/**
 * Retourne le x du svg à partir de la date + heure
 * @param dateJS: date de l'élèment à placer sur le graph - format: momentJS
 * @param heure: heure de l'élèment à placer sur le graph - format: 8 (String ou nombre)
 */
WindGraph.prototype.getXOnGraph = function(dateJS, heure) {
    // Calcul le x hors marge
    var x=0;

    // date: Chercher le nb de jour depuis aujourd'hui
    var today=moment().startOf('day');

    nbJour=dateJS.diff(today,'days');
    x+=nbJour*this.xPerDay;

    // En fonction de l'heure
    // 0->8h : ratio 10% -> 8h=10% x xPerDay
    // 8h->20h: ratio 80% -> 12h=80% x xPerDay
    // 20h->24h: ratio 10%-> 4h=10% x xPerDay
    if (heure<HoureBeginDay){
        x+=heure*0.1*this.xPerDay/HoureBeginDay;
    } else {
        if (heure<HoureEndDay) {
            x+=((heure-HoureBeginDay)*0.8/(HoureEndDay-HoureBeginDay)+0.1)*this.xPerDay;
        } else {
            x+=((heure-HoureEndDay)*0.1/(24-HoureEndDay)+0.9)*this.xPerDay;
        }
    }
    // Retourne le x du svg
    return this.getXvalue(x);
}

/**
 * Trouve la date à partir de x
 * @param x : x sur l'axe des abcisses
 * @return date correspondant à x - format: momentJS
 */
WindGraph.prototype.getDateFromX=function(x) {
    x=this.getValueX(x);

    // trouver le jour
    var numDay=Math.ceil(x/this.xPerDay)-1;

    // trouver l'heure
    var xHoure=(x-numDay*this.xPerDay);
    var nbHour=0;
    if (xHoure<=(0.1*this.xPerDay)) {
        nbHour=Math.round(HoureBeginDay*xHoure/(0.1*this.xPerDay));
    } else {
        if (xHoure<=(0.9*this.xPerDay)) {
            var xPrime=xHoure-0.1*this.xPerDay;
            nbHour=Math.round(xPrime*(HoureEndDay-HoureBeginDay)/(0.8*this.xPerDay))+HoureBeginDay;
        } else {
            var xPrime=xHoure-0.9*this.xPerDay;
            nbHour=Math.round(xPrime*(24-HoureEndDay)/(0.1*this.xPerDay))+HoureEndDay;
        }
    }
    //alert("x:"+x+"  numDay:"+numDay+"  xHoure:"+xHoure+"   xPrime:"+xPrime+"   nbHoure:"+nbHour+"   xPerDay:"+this.xPerDay+"   x pour 8h:"+(0.1*this.xPerDay));
    var dateResult=moment().startOf('day');
    dateResult.add(numDay,'days');
    dateResult.add(nbHour,'hours');
    return dateResult;
}

/**
 * Trouve les Nds à partir de y
 * @param y : y sur l'axe des ordonn�es
 * @return Nds correspondant � y - format: int
 */
WindGraph.prototype.getNdsFromY=function(y) {
    y=this.getValueY(y);
    return y/this.yPerNds;
}

/* Dessine le niveau min pour naviguer en kite */
WindGraph.prototype.drawLimiNav = function() {
    var pathMin="M "+this.getXvalue(0)+" "+this.getYOnGraph(NdsForNavMin)+" L "+this.getXvalue(this.xMax)+" "+this.getYOnGraph(NdsForNavMin);
    this.newPathSVGElement(pathMin,"ligneNdsMinForNav");
    this.newTextSVGElement(this.getXvalue(0)+5,this.getYOnGraph(NdsForNavMin)-1,"Min pour kiter","texteNdsMinForNav");
    this.newTextSVGElement(this.getXvalue(this.xMax)-50,this.getYOnGraph(NdsForNavMin)-1,"Min pour kiter","texteNdsMinForNav");
/*
    var pathMin="M "+this.getXvalue(0)+" "+this.getYOnGraph(NdsForNavGood)+" L "+this.getXvalue(this.xMax)+" "+this.getYOnGraph(NdsForNavGood);
    this.newPathSVGElement(pathMin,"ligneNdsGoodForNav");*/

    var lineGoodForNav=this.newRectSVGElement(this.getXvalue(0),this.getYOnGraph(NdsForNavGood),0,0.5,"ligneNdsGoodForNav");
    this.newAnimate('width','XML','0s','3s',0,this.xMax,lineGoodForNav,'freeze');
    this.newTextSVGElement(this.getXvalue(this.xMax)-50,this.getYOnGraph(NdsForNavGood)-2,"Vent pour kiter","texteNdsGoodForNav");
    this.newTextSVGElement(this.getXvalue(0)+5,this.getYOnGraph(NdsForNavGood)-2,"Vent pour kiter","texteNdsGoodForNav");
}

/**
 * Dessin un rectangle dans groupe Restriction Maree, pour la date de deb et date fin, � l'etat indiqu� (OK, KO, Warn)
 * @param dateBegin: sous la forme MomentJS
 * @param dateFin: sous la forme MomentJS
 * @param etatRestriction : "OK", "KO" ou "warn"
 */
WindGraph.prototype.drawRestrictionMaree = function(dateJS,heureBegin,heureEnd,etatRestriction) {
    //alert("Ajout restriction: "+dateJS+"  "+heureBegin+" -> "+heureEnd+"  dans l'�tat:"+etatRestriction);
    var xBegin=this.getXOnGraph(dateJS,heureBegin);
    var xEnd=this.getXOnGraph(dateJS,heureEnd);
    this.newRectSVGElement(xBegin,this.getYvalue(this.yMax),xEnd-xBegin,this.yMax,"restrictionMaree"+etatRestriction,null,this.mareeRestrictionGroup);
}

WindGraph.prototype.drawTooltip=function() {
    this.tooltip_bg=this.newRectSVGElement(0,0,80,33,"tooltip_bg","tooltip_bg");
    this.tooltip_bg.setAttributeNS(null, 'visibility', 'hidden');
    this.tooltip_bg.setAttributeNS(null, 'rx', '2');
    this.tooltip_bg.setAttributeNS(null, 'ry', '2');
    var tooltip=this.newTextSVGElement(0,0,null,"tooltipText");

    // ligne Date
    this.tooltipLigneDate=this.newTSpanSVGElement(0,0,null,"tootltipDateLigne",tooltip);
    this.newTSpanSVGElement(0,0,"Date: ",null,this.tooltipLigneDate);
    this.tooltipDate=this.newTSpanSVGElement(0,0,"?","tootltipDate",this.tooltipLigneDate);

    // ligne Heure
    this.tooltipLigneHeure=this.newTSpanSVGElement(0,0,null,"tootltipHeureLigne",tooltip);
    this.tooltipHeureLabel=this.newTSpanSVGElement(0,10,"Heure: ",null,this.tooltipLigneHeure);
    this.tooltipHeure=this.newTSpanSVGElement(0,0,"?","tootltipHeure",this.tooltipLigneHeure);

    // ligne vent
    var tooltipLigneNds=this.newTSpanSVGElement(0,0,null,"tootltipNdsLigne",tooltip);
    this.tooltipNdsLabel=this.newTSpanSVGElement(0,10,"Vent: ",null,tooltipLigneNds);
    this.tooltipNds=this.newTSpanSVGElement(0,0,"?","tootltipNds",tooltipLigneNds);

    tooltip.setAttributeNS(null, 'visibility', 'hidden');

    return tooltip;
}

WindGraph.prototype.setTooltipData=function(x,y) {
    this.tooltip_bg.setAttributeNS(null,"x",x-8);
    this.tooltip_bg.setAttributeNS(null,"y",y-40);

    this.tooltip.setAttributeNS(null,"x",x-5);
    this.tooltip.setAttributeNS(null,"y",y-30);

    var datePrev=svgGraph.getDateFromX(x);//format:momentJS
    var ndsPrev=Math.round(svgGraph.getNdsFromY(y));//format:int

    this.tooltipDate.firstChild.data = datePrev.format("ddd DD MMM");
    //this.tooltipHeure.setAttributeNS(null,"x",x-2);
    this.tooltipHeure.firstChild.data = datePrev.format("HH")+"h";
    this.tooltipHeureLabel.setAttributeNS(null,"dx",-this.tooltipLigneDate.getComputedTextLength());
    this.tooltipNds.firstChild.data = ndsPrev+" Nds";
    this.tooltipNdsLabel.setAttributeNS(null,"dx",-this.tooltipLigneHeure.getComputedTextLength());
}

WindGraph.prototype.drawFlecheOrientationDef=function(nameSymbol) {
    // Fleche dans un rectangle de largeur:LargeurFlecheOrient, hauteur:HauteurFlecheOrient (viewBox)
    var yHauteurFleche=HauteurFlecheOrient-1;
    var xLargeurFleche=LargeurFlecheOrient-1;
    var hauteurPointe=HauteurFlecheOrient/5;
    var yStartPointeFleche=yHauteurFleche-hauteurPointe;
    var yQueueFleche=hauteurPointe;
    var xMilieuFleche=LargeurFlecheOrient/2;

    var x0=-LargeurFlecheOrient/2;
    var y0=-HauteurFlecheOrient/2;

    var points=x0+","+y0+
        " "+(x0+xMilieuFleche)+","+(y0+yQueueFleche)+
        " "+(x0+xLargeurFleche)+","+y0+
        " "+(x0+xMilieuFleche)+","+(y0+yStartPointeFleche)+
        " "+(x0+xLargeurFleche)+","+(y0+yStartPointeFleche)+
        " "+(x0+xMilieuFleche)+","+(y0+yHauteurFleche)+
        " "+x0+","+(y0+yStartPointeFleche)+
        " "+(x0+xMilieuFleche)+","+(y0+yStartPointeFleche)+
        " "+x0+","+y0;

    var symbolFlechOrient=this.newSymbolSVGElement(nameSymbol,x0+" "+y0+" "+LargeurFlecheOrient+" "+HauteurFlecheOrient,"xMinYMin meet");//,)
    this.newPolygonSVGElement(points,'flecheOrientationGraph',symbolFlechOrient);
}
WindGraph.prototype.drawFlecheOrientation=function(deg,x) {
    var ratioFleche=LargeurFlecheOrient/HauteurFlecheOrient;
    var hautFleche=3;
    var yFleche=this.getYOnGraph(YNdsPositionFleche);
    deg=deg.toFixed(1); // For have 90.0
    this.newUseSVGElement('flecheOrientDef',x-(hautFleche*ratioFleche/2),yFleche-hautFleche,hautFleche,null, 'rotate('+deg+' '+x+' '+yFleche+' )',"flecheOrientationGraph"+orientationVentSpot[deg]);
}

WindGraph.prototype.drawMeteoIcon=function(meteoStr,x) {
    svgIcon=this.newSVGElement(x,20,35,15);
    //this.newRectSVGElement(0,0,50,50,"nuitGraph",null,svgIcon);
    var y=yIconFromName(meteoStr);
    this.newImageSVGElement(0,-y,30,600,iconUrl,svgIcon);
}

/**
 * Return the y value for display only the good icon from the file iconUrl=meteo-icons-small.png
 */
function yIconFromName(meteoStr) {
    return getTabValIcon()[meteoStr]*13.3;
}

WindGraph.prototype.drawPrecipitation=function(precipitation,x) {
    var higth=precipitation*4;
    this.newRectSVGElement(x,this.getValueY(0)-higth,3,higth,"precipitationRectGraph");
}