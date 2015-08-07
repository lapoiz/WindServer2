//Constantes
const SvgWidth= 800;
const SvgHeight= 300;
const MargeLeft= 50;
const MargeRight= 50;
const MargeBottom= 50;
const MargeTop= 20;
const NbJourPrev=10; /* prévision à nbJourPrev jours max */
const NdsMax=40;

/**
 *
 * @constructor
 */
function WindGraph(idName) {
    Graph.call(this, idName);

    /* Parametres generaux */
    this.xMax=SvgWidth-MargeLeft-MargeRight;
    this.yMax=SvgHeight-MargeBottom-MargeTop;
    this.xPerDay=this.xMax/NbJourPrev; /* largeur en x pour une journée */
    this.yPerNds=this.yMax/NdsMax; /* équivalant 1 Nd -> yPerNds pixel */

    var viewBoxParam = "0 0 "+SvgWidth+" "+SvgHeight;
    this.svg.setAttributeNS(null,'viewBox',viewBoxParam);
    this.svg.setAttributeNS(null,'preserveAspectRatio',"xMidYMid meet");

    this.svgTitle = this.createTitle('Spot');
    this.drawAxes();
    this.drawDateOnX();
    this.drawNdsOnY();
}

WindGraph.prototype = Object.create(Graph.prototype);

/* Coordonnées normales */
WindGraph.prototype.getXvalue = function(x) {
    return x+MargeLeft;
}
WindGraph.prototype.getYvalue = function(y) {
    return SvgHeight-MargeTop-y;
}

/* Créé le titre */
WindGraph.prototype.createTitle = function(titleTxt) {
    return this.newTextSVGElement(this.getXvalue(this.xMax / 2),15,titleTxt,'titleGraph');
}
WindGraph.prototype.setTitle = function(titleTxt) {
    this.svgTitle.firstChild.data=titleTxt;
}

/* Dessine les axes des X et Y */
WindGraph.prototype.drawAxes = function() {
    var chemin="M "+this.getXvalue(0)+" "+this.getYvalue(this.yMax)+" L "+this.getXvalue(0)+" "+this.getYvalue(0)+
        "L "+this.getXvalue(this.xMax)+" "+this.getYvalue(0);
    this.newPathSVGElement(chemin,'axesGraph');
}

/* Dessine les dates sur l'axe X */
WindGraph.prototype.drawDateOnX = function() {
    /* Prévision sur les 10 prochains jours max */
    moment.locale('fr');
    var now = moment();
    now.add(-1, 'days'); /* Pour commencer à aujourd'hui dans la boucle */

    /* créer la defs pour la nuit */
    var linearGradient=this.newLinearGradientSVGElement("degradeNuit","0","0","100%","0%");
    this.addStopLinearGradientSVGElement(linearGradient,'stop1','0%');
    this.addStopLinearGradientSVGElement(linearGradient,'stop2','10%');
    this.addStopLinearGradientSVGElement(linearGradient,'stop3','90%');
    this.addStopLinearGradientSVGElement(linearGradient,'stop4','100%');

    var xCurrent=this.xPerDay/2;
    var jour;
    for (i = 0; i < NbJourPrev; i++) {
        now.add(1, 'days');
        jour=now.format("ddd D");

        /* Créer les nuits entre les dates */
        var rectNight=this.newRectSVGElement(this.getXvalue(i*this.xPerDay),this.getYvalue(this.yMax),this.xPerDay,
            this.yMax,'nuitGraph',null);
        rectNight.setAttributeNS(null,"fill","url(#degradeNuit)");

        /* Ecrit la date */
        this.newTextSVGElement(this.getXvalue(xCurrent),this.getYvalue(-15),jour,"dateAxesGraph");
        xCurrent+=this.xPerDay;

        /* Créer les lignes séparant les jours */
        var path="M "+this.getXvalue((i+1)*this.xPerDay)+" "+this.getYvalue(-20)+" L "+this.getXvalue((i+1)*this.xPerDay)
            +" "+this.getYvalue(this.yMax);
        this.newPathSVGElement(path,"subAxesDateGraph");
    }
}

WindGraph.prototype.drawNdsOnY = function() {
    /* Créer les lignes séparant les Nds */
    var nbEntreNdsPrincipaux=10;
    var nbNdsPrincipal = Math.ceil(NdsMax/nbEntreNdsPrincipaux);
    for (i = 1; i <= nbNdsPrincipal; i++) {
        if (i*nbEntreNdsPrincipaux<=NdsMax) {
            var path = "M " + this.getXvalue(-5) + " " + this.getYvalue(i*nbEntreNdsPrincipaux*this.yPerNds) + " L " +
                this.getXvalue(this.xMax) + " " + this.getYvalue(i*nbEntreNdsPrincipaux*this.yPerNds);
            this.newPathSVGElement(path, "subAxesNdsGraph");
            this.newTextSVGElement(this.getXvalue(-15),this.getYvalue(i*nbEntreNdsPrincipaux*this.yPerNds-4),
                i*nbEntreNdsPrincipaux,"ndsAxesGraph");
        } // else : ca ne tombe pas pile -> on n'affiche pas la valeur haute au dessus de NdsMax
    }
}

/**
 * Retourne le x du svg à partir de la date
 * @param date: date de l'élément à placer sur le graph
 */
WindGraph.prototype.getXOnGraph = function(date) {
    // Calcul le x hors marge
    var x=0;
    // Chercher le nb de jour depuis aujourd'hui

    // En fonction de l'heure




    // Retourne le x du svg
    return this.getXvalue(x);
}