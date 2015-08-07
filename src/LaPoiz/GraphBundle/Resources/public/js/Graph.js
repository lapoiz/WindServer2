const SvgNS='http://www.w3.org/2000/svg';

/**
 *
 * @constructor
 */
function Graph(idName) {
    this.idName=idName;
    this.svg=document.getElementById(this.idName);
    this.defsSvgGraph=document.createElementNS(SvgNS,'defs');
    this.svg.appendChild(this.defsSvgGraph);
}

isEmpty = function(variable) {
    return variable === undefined || variable === null || variable === '' || variable.length === 0;
}

Graph.prototype.newPathSVGElement = function(path,className,svgElem) {
    var newPath=document.createElementNS(SvgNS,'path');
    newPath.setAttributeNS(null,'d',path);
    if (!isEmpty(className)) {
        newPath.setAttributeNS(null,'class',className);
    }
    if (isEmpty(svgElem)) {
        this.svg.appendChild(newPath);
    } else {
        svgElem.appendChild(newPath);
    }

    return newPath;
}
Graph.prototype.newRectSVGElement = function(x,y,width,height,className,idName,svgElem) {
    var newRect=document.createElementNS(SvgNS,'rect');
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
    if (isEmpty(svgElem)) {
        this.svg.appendChild(newRect);
    } else {
        svgElem.appendChild(newRect);
    }
    return newRect;
}
Graph.prototype.newTextSVGElement = function(x,y,text,className,svgElem) {
    var newText=document.createElementNS(SvgNS,'text');
    newText.setAttributeNS(null,'x',x);
    newText.setAttributeNS(null,'y',y);
    newText.appendChild(document.createTextNode(text));
    if (!isEmpty(className)) {
        newText.setAttributeNS(null,'class',className);
    }
    if (isEmpty(svgElem)) {
        this.svg.appendChild(newText);
    } else {
        svgElem.appendChild(newText);
    }
    return newText;
}
Graph.prototype.newGroupSVGElement = function(x,y,width,height,className,idName,svgElem) {
    var newGroup=document.createElementNS(SvgNS,'g');
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
    if (isEmpty(svgElem)) {
        this.svg.appendChild(newGroup);
    } else {
        svgElem.appendChild(newGroup);
    }
    return newGroup;
}
Graph.prototype.newLinearGradientSVGElement = function(idName, x1, y1, x2, y2,svgElem) {
    var newLinearGradient=document.createElementNS(SvgNS,'linearGradient');
    newLinearGradient.setAttributeNS(null,'x1',x1);
    newLinearGradient.setAttributeNS(null,'y1',y1);
    newLinearGradient.setAttributeNS(null,'x2',x2);
    newLinearGradient.setAttributeNS(null,'y2',y2);
    newLinearGradient.setAttributeNS(null,'id',idName);

    if (isEmpty(svgElem)) {
        this.defsSvgGraph.appendChild(newLinearGradient);
    } else {
        svgElem.appendChild(newLinearGradient);
    }
    return newLinearGradient;
}
Graph.prototype.addStopLinearGradientSVGElement = function(svgElem, idName, offset) {
    var newstop=document.createElementNS(SvgNS,'stop');
    newstop.setAttributeNS(null,'id',idName);
    newstop.setAttributeNS(null,'offset',offset);
    svgElem.appendChild(newstop);
    return newstop;
}




