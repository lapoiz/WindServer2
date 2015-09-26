
function Orientation(svgChart, website) {
	this.svgChart=svgChart;
	this.website=website;

	this.svg=svgChart.svg;

}

Spline.prototype.addPoint = function(x,y){
	this.arrayOfPoints.push([x,y]);
}
