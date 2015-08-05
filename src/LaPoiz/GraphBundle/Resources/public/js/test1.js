//Constantes
const svgNS='http://www.w3.org/2000/svg';

const svgWidth= 800;
const svgHeight= 300;
const margeLeft= 50;
const margeRight= 50;
const margeBottom= 50;
const margeTop= 20;
const nbJourPrev=10; /* prévision à nbJourPrev jours max */

var xMax=0;
var yMax=0;
var svgGraph;
var svgLegend;
var defsSvgGraph;
var xPerDay;


var isEmpty = function(variable) {
    return variable === undefined || variable === null || variable === '' || variable.length === 0;
}


/* Initialise le Graph */
function initGraph() {
    /* Parametres generaux */
    xMax=svgWidth-margeLeft-margeRight;
    yMax=svgHeight-margeBottom-margeTop;
    xPerDay = xMax/nbJourPrev; /* largeur en x pour une journée */

    /* init les svg */
    svgGraph=document.getElementById("graphSVG");
    svgLegend=document.getElementById("legendSVG");
    defsSvgGraph=document.createElementNS(svgNS,'defs');
    svgGraph.appendChild(defsSvgGraph);

    var viewBoxParam = "0 0 "+svgWidth+" "+svgHeight;
    svgGraph.setAttributeNS(null,'viewBox',viewBoxParam);
    svgGraph.setAttributeNS(null,'preserveAspectRatio',"xMidYMid meet");

    /* Le titre */
    var spotName = document.getElementById("spotName");
    spotName.setAttributeNS(null,'x',getXvalue(xMax/2));
    spotName.setAttributeNS(null,'y',getYvalue(yMax-20));

    drawAxes();
    drawDateOnX();
}

/* Coordonnées normales */
function getXvalue(x) {
    return x+margeLeft;
}
function getYvalue(y) {
    return svgHeight-margeBottom-margeTop-y;
}

/* Dessine les axes des X et Y */
function drawAxes() {
    var axes = document.createElementNS(svgNS,'path');
    var chemin="M "+getXvalue(0)+" "+getYvalue(yMax)+" L "+getXvalue(0)+" "+getYvalue(0)+
                "L "+getXvalue(xMax)+" "+getYvalue(0);
    axes.setAttributeNS(null,'class',"axesGraph");
    axes.setAttributeNS(null,'d',chemin);
    svgGraph.appendChild(axes);
}

/* Dessine les dates sur l'axe X */
function drawDateOnX() {
    /* Prévision sur les 10 prochains jours max */
    moment.locale('fr');
    var now = moment();
    now.add(-1, 'days'); /* Pour commencer à aujourd'hui dans la boucle */

    /* créer la defs pour la nuit */
    var linearGradient=newLinearGradientSVGElement(defsSvgGraph,"degradeNuit","0%","0%","100%","0%");
    addStopLinearGradientSVGElement(linearGradient,'stop1','0%');
    //addStopLinearGradientSVGElement(linearGradient,'stop2','10%');
    //addStopLinearGradientSVGElement(linearGradient,'stop3','90%');
    addStopLinearGradientSVGElement(linearGradient,'stop4','100%');

    var xCurrent=xPerDay/2;
    for (i = 0; i < nbJourPrev; i++) {
        now.add(1, 'days');
        jour=now.format("ddd D");
        newTextSVGElement(svgGraph,getXvalue(xCurrent),getYvalue(-15),jour,"dateAxesGraph");
        xCurrent+=xPerDay;

        /* Créer les lignes séparant les jours */
        var path="M "+getXvalue((i+1)*xPerDay)+" "+getYvalue(0)+" L "+getXvalue((i+1)*xPerDay)+" "+getYvalue(yMax);
        newPathSVGElement(svgGraph,path,"subAxesDateGraph");

        /* Créer les nuits entre les dates */
        newRectSVGElement(svgGraph,getXvalue(i*xPerDay),getYvalue(yMax),xPerDay,yMax,'nuitGraph',null);
    }


}



/* Use the JSon */
function putOnGraph(jSon) {

    /* Put title name */
    $("#spotName").text(jSon.spot);

    /* Get website */
    var listeSites = jSon.listSites;
    for (i = 0; i < listeSites.length; i++) {
        addWebsite(listeSites[i], i);
    }
}

function addWebsite(site, numSite) {
    addWebsiteOnLegend(site, numSite);
}

function addWebsiteOnLegend(site, numSite) {
    var newGroupLegSite=newGroupSVGElement(svgLegend, 10,(numSite*20),'100%','20px','rectSiteLegend',"legend_site_"+site.nom);
    newTextSVGElement(newGroupLegSite,10,((numSite+1)*20),site.nom,'siteLegend');
    newTextSVGElement(newGroupLegSite,100,((numSite+1)*20),site.date,'siteUpdateLegend');
}

function newPathSVGElement(svgElem,path,className) {
    var newPath=document.createElementNS(svgNS,'path');
    newPath.setAttributeNS(null,'d',path);
    if (!isEmpty(className)) {
        newPath.setAttributeNS(null,'class',className);
    }
    svgElem.appendChild(newPath);
    return newPath;
}
function newRectSVGElement(svgElem,x,y,width,height,className,idName) {
    var newRect=document.createElementNS(svgNS,'rect');
    newRect.setAttributeNS(null,'x',x);
    newRect.setAttributeNS(null,'y',y);
    if (!isEmpty(width)) {
        newRect.setAttributeNS(null,'width',width);
    }
    if (!isEmpty(height)) {
        newRect.setAttributeNS(null,'height',height);
    }
    if (!isEmpty(className)) {
        newRect.setAttributeNS(null,'class',className);
    }
    if (!isEmpty(idName)) {
        newRect.setAttributeNS(null,'id',idName);
    }

    //newRect.setAttributeNS(null,'style','fill="url(#degradeNuit)"');
    svgElem.appendChild(newRect);
    return newRect;
}
function newTextSVGElement(svgElem,x,y,text,className) {
    var newText=document.createElementNS(svgNS,'text');
    newText.setAttributeNS(null,'x',x);
    newText.setAttributeNS(null,'y',y);
    newText.appendChild(document.createTextNode(text));
    if (!isEmpty(className)) {
        newText.setAttributeNS(null,'class',className);
    }
    svgElem.appendChild(newText);
    return newText;
}
function newGroupSVGElement(svgElem,x,y,width,height,className,idName) {
    var newGroup=document.createElementNS(svgNS,'g');
    newGroup.setAttributeNS(null,'x',x);
    newGroup.setAttributeNS(null,'y',y);
    if (!isEmpty(width)) {
        newGroup.setAttributeNS(null,'width',width);
    }
    if (!isEmpty(height)) {
        newGroup.setAttributeNS(null,'height',height);
    }
    if (!isEmpty(className)) {
        newGroup.setAttributeNS(null,'class',className);
    }
    if (!isEmpty(idName)) {
        newGroup.setAttributeNS(null,'id',idName);
    }
    svgElem.appendChild(newGroup);
    return newGroup;
}
function newLinearGradientSVGElement(svgElem, idName, x1, y1, x2, y2) {
    var newLinearGradient=document.createElementNS(svgNS,'linearGradient');
    newLinearGradient.setAttributeNS(null,'x1',x1);
    newLinearGradient.setAttributeNS(null,'y1',y1);
    newLinearGradient.setAttributeNS(null,'x2',x2);
    newLinearGradient.setAttributeNS(null,'y2',y2);
    newLinearGradient.setAttributeNS(null,'id',idName);

    svgElem.appendChild(newLinearGradient);
    return newLinearGradient;
}
function addStopLinearGradientSVGElement(svgElem, idName, offset) {
    var newstop=document.createElementNS(svgNS,'stop');
    newstop.setAttributeNS(null,'id',idName);
    newstop.setAttributeNS(null,'offset',offset);
    svgElem.appendChild(newstop);
    return newstop;
}