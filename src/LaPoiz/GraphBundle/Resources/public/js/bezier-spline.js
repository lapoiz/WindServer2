
function Spline(svgElem) {
	this.svg=svgElem;

	this.spline=new Array(); /*splines*/
	this.spline[0]= this.createPath("blue");
	this.spline[1]= this.createPath("blue");
	this.spline[2]= this.createPath("blue");
	this.spline[3]= this.createPath("blue");

	this.vert=new Array(); /*vertices*/
	this.current; 	/*current object*/
	this.x0;
	this.y0;	/*svg offset*/
}


Spline.prototype.drawPoint = function(){
	/*create control points*/
	this.vert[0] = this.createKnot(50,50);
	this.vert[1] = this.createKnot(100,70);
	this.vert[2] = this.createKnot(200,50);
	this.vert[3] = this.createKnot(300,90);
	
	this.updateSplines();
}
/*creates and adds an SVG circle to represent knots*/
Spline.prototype.createKnot=function(x,y) {
	var circle=document.createElementNS("http://www.w3.org/2000/svg","circle");
	circle.setAttributeNS(null,"r",10);
	circle.setAttributeNS(null,"cx",x);
	circle.setAttributeNS(null,"cy",y);
	circle.setAttributeNS(null,"fill","gold");
	circle.setAttributeNS(null,"stroke","black");
	circle.setAttributeNS(null,"stroke-width","4");
	circle.setAttributeNS(null,"onmousedown","startMove(evt)");
	this.svg.appendChild(circle);
	return circle;
}

/*creates and adds an SVG path without defining the nodes*/
Spline.prototype.createPath=function(color,width) {
	width = (typeof width == 'undefined' ? "8" : width);
	var path=document.createElementNS("http://www.w3.org/2000/svg","path");
	path.setAttributeNS(null,"fill","none");
	path.setAttributeNS(null,"stroke",color);
	path.setAttributeNS(null,"stroke-width",width);

	this.svg.appendChild(path);
	return path;
}

/*computes spline control points*/
Spline.prototype.updateSplines=function()
{	
	/*grab (x,y) coordinates of the control points*/
	x=new Array();
	y=new Array();
	for (i=0;i<4;i++)
	{
		/*use parseInt to convert string to int*/
		x[i]=parseInt(this.vert[i].getAttributeNS(null,"cx"));
		y[i]=parseInt(this.vert[i].getAttributeNS(null,"cy"));
	}
	
	/*computes control points p1 and p2 for x and y direction*/
	px = this.computeControlPoints(x);
	py = this.computeControlPoints(y);
	
	/*updates path settings, the browser will draw the new spline*/
	for (i=0;i<3;i++)
		this.spline[i].setAttributeNS(null,"d",
			this.path(x[i],y[i],px.p1[i],py.p1[i],px.p2[i],py.p2[i],x[i+1],y[i+1]));
}

/*creates formated path string for SVG cubic path element*/
Spline.prototype.path=function(x1,y1,px1,py1,px2,py2,x2,y2)
{
	return "M "+x1+" "+y1+" C "+px1+" "+py1+" "+px2+" "+py2+" "+x2+" "+y2;
}

/*computes control points given knots K, this is the brain of the operation*/
Spline.prototype.computeControlPoints=function(K)
{
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
Spline.prototype.getOffset=function( el )
{
    var _x = 0;
    var _y = 0;
    while( el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop ) ) {
        _x += el.offsetLeft - el.scrollLeft;
        _y += el.offsetTop - el.scrollTop;
        el = el.offsetParent;
    }
    return { top: _y, left: _x };
}