const SvgNS='http://www.w3.org/2000/svg';
const SvgXlinkns='http://www.w3.org/1999/xlink';

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

// X et Y selon les normes classiques du svg, cad O : en haut � gauche

Graph.prototype.newPolygonSVGElement = function(points,className,svgElem) {
    var newPolygon=document.createElementNS(SvgNS,'polygon');
    if (!isEmpty(points)) {
        newPolygon.setAttributeNS(null,'points',points);
    }
    if (!isEmpty(className)) {
        newPolygon.setAttributeNS(null,'class',className);
    }
    if (isEmpty(svgElem)) {
        this.svg.appendChild(newPolygon);
    } else {
        svgElem.appendChild(newPolygon);
    }

    return newPolygon;
}
Graph.prototype.newPathSVGElement = function(path,className,svgElem) {
    var newPath=document.createElementNS(SvgNS,'path');
    if (!isEmpty(path)) {
        newPath.setAttributeNS(null,'d',path);
    }
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
Graph.prototype.newCircleSVGElement = function(x,y,rayon,className,idName,svgElem) {
    var newCircle=document.createElementNS(SvgNS,'circle');
    newCircle.setAttributeNS(null,'cx',x);
    newCircle.setAttributeNS(null,'cy',y);
    if (!isEmpty(rayon)) {
        newCircle.setAttributeNS(null,'r',rayon);
    }
    if (!isEmpty(className)) {
        newCircle.setAttributeNS(null,'class',className);
    }
    if (!isEmpty(idName)) {
        newCircle.setAttributeNS(null,'id',idName);
    }
    if (isEmpty(svgElem)) {
        this.svg.appendChild(newCircle);
    } else {
        svgElem.appendChild(newCircle);
    }
    return newCircle;
}
Graph.prototype.newTextSVGElement = function(x,y,text,className,svgElem,idName) {
    var newText=document.createElementNS(SvgNS,'text');
    newText.setAttributeNS(null,'x',x);
    newText.setAttributeNS(null,'y',y);
    if (!isEmpty(text)) {
        newText.appendChild(document.createTextNode(text));
    }
    if (!isEmpty(className)) {
        newText.setAttributeNS(null,'class',className);
    }
    if (isEmpty(svgElem)) {
        this.svg.appendChild(newText);
    } else {
        svgElem.appendChild(newText);
    }

    if (!isEmpty(idName)) {
        newText.setAttributeNS(null,'id',idName);
    }
    return newText;
}
Graph.prototype.newTSpanSVGElement = function(dx,dy,text,className,svgElem,idName) {
    var newTspan=document.createElementNS(SvgNS,'tspan');
    if (!isEmpty(dx)) {
        newTspan.setAttributeNS(null,'dx',dx);
    }
    if (!isEmpty(dy)) {
        newTspan.setAttributeNS(null,'dy',dy);
    }
    if (!isEmpty(text)) {
        newTspan.appendChild(document.createTextNode(text));
    }
    if (!isEmpty(className)) {
        newTspan.setAttributeNS(null,'class',className);
    }
    if (isEmpty(svgElem)) {
        this.svg.appendChild(newTspan);
    } else {
        svgElem.appendChild(newTspan);
    }

    if (!isEmpty(idName)) {
        newTspan.setAttributeNS(null,'id',idName);
    }
    return newTspan;
}
Graph.prototype.newGroupSVGElement = function(x,y,idName,width,height,className,svgElem) {
    var newGroup=document.createElementNS(SvgNS,'g');
    if (!isEmpty(x)) {
        newGroup.setAttributeNS(null,'x',x);
    }
    if (!isEmpty(y)) {
        newGroup.setAttributeNS(null,'y',y);
    }
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
Graph.prototype.newAnimate=function(attributeName,attributeType,begin,dur,from,to,svgElem,fill) {
    var animate=document.createElementNS(SvgNS,'animate');
    animate.setAttributeNS(null,'attributeName',attributeName);
    animate.setAttributeNS(null,'attributeType',attributeType);
    animate.setAttributeNS(null,'begin',begin);
    animate.setAttributeNS(null,'dur',dur);
    animate.setAttributeNS(null,'from',from);
    animate.setAttributeNS(null,'to',to);
    if (!isEmpty(fill)) {
        animate.setAttributeNS(null,'fill',fill);
    }

    svgElem.appendChild(animate);
}
Graph.prototype.newSymbolSVGElement = function(idName,viewBox,preserveAspectRatio) {
    var newSymbol=document.createElementNS(SvgNS,'symbol');
    if (!isEmpty(idName)) {
        newSymbol.setAttributeNS(null,'id',idName);
    }
    if (!isEmpty(viewBox)) {
        newSymbol.setAttributeNS(null,'viewBox',viewBox);
    } else {
        newSymbol.setAttributeNS(null,'viewBox','0 0 100 100');
    }

    if (!isEmpty(preserveAspectRatio)) {
        newSymbol.setAttributeNS(null,'preserveAspectRatio',preserveAspectRatio);
    } else {
        newSymbol.setAttributeNS(null,'preserveAspectRatio','xMidYMid meet');
    }
    this.defsSvgGraph.appendChild(newSymbol);
    return newSymbol;
}
Graph.prototype.newUseSVGElement = function(symbolName,x,y,width,height,transform,className,svgElem) {
    var newUse=document.createElementNS(SvgNS,'use');
    if (!isEmpty(symbolName)) {
        newUse.setAttributeNS(SvgXlinkns,'xlink:href','#'+symbolName);
    }
    if (!isEmpty(x)) {
        newUse.setAttributeNS(null,'x',x);
    }
    if (!isEmpty(y)) {
        newUse.setAttributeNS(null,'y',y);
    }
    if (!isEmpty(width)) {
        newUse.setAttributeNS(null,'width',width);
    }
    if (!isEmpty(height)) {
        newUse.setAttributeNS(null,'height',height);
    }
    if (!isEmpty(transform)) {
        newUse.setAttributeNS(null,'transform',transform);
    }

    if (!isEmpty(className)) {
        newUse.setAttributeNS(null,'class',className);
    }

    if (!isEmpty(svgElem)) {
        svgElem.appendChild(newUse);
    } else {
        this.svg.appendChild(newUse)
    }
    return newUse;
}
Graph.prototype.newImageSVGElement = function(x,y,width,height,href,svgElem) {
    var newImage=document.createElementNS(SvgNS,'image');

    if (!isEmpty(x)) {
        newImage.setAttributeNS(null,'x',x);
    }
    if (!isEmpty(y)) {
        newImage.setAttributeNS(null,'y',y);
    }
    if (!isEmpty(width)) {
        newImage.setAttributeNS(null,'width',width);
    }
    if (!isEmpty(height)) {
        newImage.setAttributeNS(null,'height',height);
    }
    if (!isEmpty(href)) {
        newImage.setAttributeNS(SvgXlinkns,'href',href);
    }
    if (!isEmpty(svgElem)) {
        svgElem.appendChild(newImage);
    } else {
        this.svg.appendChild(newImage)
    }

    return newImage;
}
Graph.prototype.newSVGElement = function(x,y,width,height,viewBox,svgElem) {
    var newSvg=document.createElementNS(SvgNS,'svg');

    if (!isEmpty(x)) {
        newSvg.setAttributeNS(null,'x',x);
    }
    if (!isEmpty(y)) {
        newSvg.setAttributeNS(null,'y',y);
    }
    if (!isEmpty(width)) {
        newSvg.setAttributeNS(null,'width',width);
    }
    if (!isEmpty(height)) {
        newSvg.setAttributeNS(null,'height',height);
    }
    if (!isEmpty(viewBox)) {
        newSvg.setAttributeNS(null,'viewBox',viewBox);
    }
    if (!isEmpty(svgElem)) {
        svgElem.appendChild(newSvg);
    } else {
        this.svg.appendChild(newSvg)
    }
    //newSvg.style.display = "block";
    return newSvg;
}


