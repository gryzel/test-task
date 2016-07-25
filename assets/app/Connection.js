var Connection = (function(){

	function Connection(url) {

    	this.open = false;

    	this.socket = new WebSocket("ws://" + url);
    	this.setupConnectionEvents();
  	}

	Connection.prototype = {
		setupConnectionEvents : function () {
      		var self = this;

      		self.socket.onopen = function(evt) { self.connectionOpen(evt); };
      		self.socket.onmessage = function(evt) { self.connectionMessage(evt); };
      		self.socket.onclose = function(evt) { self.connectionClose(evt); };
    	},

    	connectionOpen : function(evt){
      		this.open = true;
      		this.addSystemMessage("Connected");
		},
    	connectionMessage : function(evt){
      		var data = JSON.parse(evt.data);
				console.log(data);
				var evt = $.Event(data.name);
				evt.state = data.data;		
				$(window).trigger(evt);			
        	
    	},
    	connectionClose : function(evt){
      		this.open = false;
      		this.addSystemMessage("Disconnected");
    	},

    	sendMsg : function(name,message){
    		
        	this.socket.send(JSON.stringify({
          		name : name,
          		data : message,
        	}));
		},

    	
		
    	addSystemMessage : function(msg){
			
      		console.log(msg);
    	}
  	};

  	return Connection;

})();

//var t = new Connection(Broadcast.BROADCAST_URL+":"+Broadcast.BROADCAST_PORT);
/*
var typeData = { broadType : Broadcast.POST, data : data.postData};
				$rootScope.conn.sendMsg(typeData);
*/