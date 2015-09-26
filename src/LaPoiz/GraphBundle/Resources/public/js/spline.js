
function Spline(svgChart,websiteCode) {
	this.svgChart=svgChart;
	this.websiteCode=websiteCode;

	this.svg=svgChart.svg;

	this.spline=new Array(); /*splines*/
	this.points=new Array(); /*vertices*/
	this.arrayOfPoints=new Array();
}

Spline.prototype.addPoint = function(x,y){
	this.arrayOfPoints.push([x,y]);
}

Spline.prototype.drawSpline = function() {
	// On dessine d'abord les lignes
	for (i=0;i<this.arrayOfPoints.length;i++) {
		this.spline.push(this.svgChart.newPathSVGElement(null, "courbePrev" + this.websiteCode,this.svgChart.splineGroup));
	}
	// Puis tous les points
	for (i=0;i<this.arrayOfPoints.length;i++) {
		var point=this.svgChart.newCircleSVGElement(this.arrayOfPoints[i][0],this.arrayOfPoints[i][1],1.5,"point"+this.websiteCode,null,this.svgChart.pointsGroup);
		point.addEventListener("mouseover", showTooltip, false);
		point.addEventListener("mouseout", hideTooltip, false);
		this.points.push(point);
	}
	this.updateSplines();
}

/*computes spline control points*/
Spline.prototype.updateSplines=function() {
	/*grab (x,y) coordinates of the control points*/
	x=new Array();
	y=new Array();
	for (i=0;i<this.points.length;i++)	{
		/*use parseInt to convert string to int*/
		x[i]=parseInt(this.points[i].getAttributeNS(null,"cx"));
		y[i]=parseInt(this.points[i].getAttributeNS(null,"cy"));
	}
	
	/*computes control points p1 and p2 for x and y direction*/
	px = this.computeControlPoints(x);
	py = this.computeControlPoints(y);
	
	/*updates path settings, the browser will draw the new spline*/
	for (i=0;i<this.points.length-1;i++)
		this.spline[i].setAttributeNS(null,"d",
			this.path(x[i],y[i],px.p1[i],py.p1[i],px.p2[i],py.p2[i],x[i+1],y[i+1]));
}

/*creates formated path string for SVG cubic path element*/
Spline.prototype.path=function(x1,y1,px1,py1,px2,py2,x2,y2) {
	return "M "+x1+" "+y1+" C "+px1+" "+py1+" "+px2+" "+py2+" "+x2+" "+y2;
}

/*computes control points given knots K, this is the brain of the operation*/
Spline.prototype.computeControlPoints=function(K) {
	p1=new Array();
	p2=new Array();
	n = K.length-1;
	
	/*rhs vector*/
	a=new Array();
	b=new Array();
	c=new Array();
	r=new Array();
	
	/*left most segment*/
	a[0]=0;
	b[0]=2;
	c[0]=1;
	r[0] = K[0]+2*K[1];
	
	/*internal segments*/
	for (i = 1; i < n - 1; i++)
	{
		a[i]=1;
		b[i]=4;
		c[i]=1;
		r[i] = 4 * K[i] + 2 * K[i+1];
	}
			
	/*right segment*/
	a[n-1]=2;
	b[n-1]=7;
	c[n-1]=0;
	r[n-1] = 8*K[n-1]+K[n];
	
	/*solves Ax=b with the Thomas algorithm (from Wikipedia)*/
	for (i = 1; i < n; i++)
	{
		m = a[i]/b[i-1];
		b[i] = b[i] - m * c[i - 1];
		r[i] = r[i] - m*r[i-1];
	}
 
	p1[n-1] = r[n-1]/b[n-1];
	for (i = n - 2; i >= 0; --i)
		p1[i] = (r[i] - c[i] * p1[i+1]) / b[i];
		
	/*we have p1, now compute p2*/
	for (i=0;i<n-1;i++)
		p2[i]=2*K[i+1]-p1[i+1];
	
	p2[n-1]=0.5*(K[n]+p1[n-1]);
	
	return {p1:p1, p2:p2};
}

/*code from http://stackoverflow.com/questions/442404/dynamically-retrieve-html-element-x-y-position-with-javascript*/
Spline.prototype.getOffset=function( el ) {
    var _x = 0;
    var _y = 0;
    while( el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop ) ) {
        _x += el.offsetLeft - el.scrollLeft;
        _y += el.offsetTop - el.scrollTop;
        el = el.offsetParent;
    }
    return { top: _y, left: _x };
}