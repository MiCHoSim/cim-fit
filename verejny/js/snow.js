deg_sin = function(deg) {
    return Math.sin((deg % 360) * Math.PI / 180);
};

deg_cos = function(deg) {
    return Math.cos((deg % 360) * Math.PI / 180);
};

lengthdir_x = function(len, deg) {
    return len * deg_cos(deg % 360);
};

lengthdir_y = function(len, deg) {
    return -len * deg_sin(deg % 360);
};

var SnowFlakeLastId = -1;

var SnowFlake = function(x)
{
	this.image = 0;

	if(x == undefined)
		this.x = Math.floor(Math.random() * ($(document).width() - 224)) + 64;
	else
		this.x = x;
	this.y = 0 - Math.floor(Math.random() * $(document).height());

	SnowFlakeLastId++;
	this.id = SnowFlakeLastId;

	this.animationD = Math.floor(Math.random() * 360);

	this.getDSin = function()
	{
		return deg_sin(this.animationD);
	};

	this.size = Math.floor(Math.random() * 32);

	this.reset = function()
	{
		this.y = 0;
		this.size = Math.floor(Math.random() * 32);
		this.x = Math.floor(Math.random() * ($(document).width() - 224)) + 64;
	};
};

var snowFlakes = new Array();

$(document).ready(function()
{
var count = Number($("#snow_count").text());

if(count == NaN)
	count = 20;

var image1_src, image2_src, image3_src;
var image_count;

if(document.getElementById("snow_image") != null)
{
	if(document.getElementById("snow_image_2") != null)
	{
		if(document.getElementById("snow_image_3") != null)
		{
			image_count = 3;
			image3_src = $("#snow_image_3").text();
			image2_src = $("#snow_image_2").text();
			image1_src = $("#snow_image").text();
		}
		else
		{
			image_count = 2;
			image2_src = $("#snow_image_2").text();
			image1_src = $("#snow_image").text();
		}
	}
	else
	{
		image_count = 1;
		image1_src = $("#snow_image").text();
	}
}
else
{
	image_count = 1;
	image1_src = "snow_image.png";
}

$("#snow_image").css("display", "none");
$("#snow_image_2").css("display", "none");
$("#snow_image_3").css("display", "none");

$("#snow_count").css("display", "none");

//vytvoření vloček

var currentFlake;

var snow_buffer = document.createElement("div");

snow_buffer.setAttribute("id", "snow_buffer");

document.getElementsByTagName("body")[0].appendChild(snow_buffer);

for(var i = 0; i < count; i++)

{
	snowFlakes.push(new SnowFlake());

	currentFlake = document.createElement("img");


	if(image_count == 1)
		currentFlake.setAttribute("src", image1_src);

	else if(image_count == 2)
	{
		if(Math.floor(Math.random() * 2) == 1)
			currentFlake.setAttribute("src", image1_src);

		else
			currentFlake.setAttribute("src", image2_src);
	}
	else if(image_count == 3)
	{
		var image_id = Math.floor(Math.random() * 3);
		if(image_id == 1)
			currentFlake.setAttribute("src", image1_src);

		else if(image_id == 2)
			currentFlake.setAttribute("src", image2_src);
		else
			currentFlake.setAttribute("src", image3_src);
	}

	//currentFlake.setAttribute("src", $("#snow_image").text());


	currentFlake.setAttribute("id", "snowFlake" + snowFlakes[i].id.toString());

	currentFlake.setAttribute("style", "position: absolute;left: "+snowFlakes[i].x+"px;top: 0;")
;
	snow_buffer.appendChild(currentFlake);

	snowFlakes[i].image = currentFlake;
}

var mainloop = function()
{
	var wHeight = $(document).height();

	for(var i = 0; i < count; i++)
	{
		snowFlakes[i].y += 2;
		if(snowFlakes[i].y > (wHeight - 40))
			snowFlakes[i].reset();
	}

	//nastavení vlastností vloček
	var _css, real_x, real_y;
	for(var i = 0; i < count; i++)
	{
		real_x = snowFlakes[i].x + snowFlakes[i].getDSin() * 3 * snowFlakes[i].size + 32;
		real_y = snowFlakes[i].y;
		snowFlakes[i].animationD++;
		if(snowFlakes[i].animationD > 359)
			snowFlakes[i].animationD %= 360;
		_css = "left:";
		_css += real_x.toString();
		_css += "px;top:";
		_css += real_y.toString();
		_css += "px;width:";
		_css += snowFlakes[i].size;
		_css += "px;height:";
		_css += snowFlakes[i].size;
		_css += "px;position:absolute;z-index:5000;";
		snowFlakes[i].image.setAttribute("style", _css)
;
	}
};

var animFrame = document.requestAnimationFrame || document.webkitRequestAnimationFrame || document.mozRequestAnimationFrame || document.oRequestAnimationFrame || document.msRequestAnimationFrame || null ;
if ( animFrame !== null)
{
var recursiveAnim = function(){
mainloop();
animFrame( recursiveAnim );
}
animFrame( recursiveAnim );
}else{
var ONE_FRAME_TIME = 100.0 / 6.0 ;
setInterval( mainloop, ONE_FRAME_TIME );
}
});