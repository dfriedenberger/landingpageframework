
var Tracking = {};
Tracking.GameTracker = function (id) {
    this.id = id;
	this.raise("PAGELOAD","userdate = " + new Date().toISOString());
}

Tracking.GameTracker.prototype.raise = function (event,args) {
   this.post({userid : this.id , event : event, args : args});
}

Tracking.GameTracker.prototype.set = function (key,value) {
    this.post({userid : this.id , key : key, value : value});
}

Tracking.GameTracker.prototype.post = function (data) {

    
	var request = $.ajax({
			url: "/tracking",
			type: "POST",
			data: data
		});

		request.done(function(msg) {
		 	console.log( msg );
		});

		request.fail(function(jqXHR, textStatus) {
			console.log( textStatus );
		});

}





$(function(){
	

	var userid = $("#config").data("guid");
	var gametracker = new Tracking.GameTracker(userid);
	
	function clickListener(e) 
	{   
		var el = (window.event) ? window.event.srcElement : e.target;
        var info = jQuery(el).prop("tagName");
		for (var att, i = 0, atts = el.attributes, n = atts.length; i < n; i++){
			att = atts[i];
			info += " "+att.nodeName + "='" + att.nodeValue+"'";
			
		}
		
		var text = jQuery(el)
		.clone()    //clone the element
		.children() //select all the children
		.remove()   //remove all the children
		.end()  //again go back to selected element
		.text()
		.replace(/\n/g, ""); 
		
		info += " text='"+text+"'";
		
		gametracker.raise("CLICK",info);
	}
	
	document.onclick = clickListener;
	
	$(window).on("unload", function(e) {
		gametracker.raise("UNLOAD","");
	});
	
	

});
