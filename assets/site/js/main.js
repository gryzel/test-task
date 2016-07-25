// зарезервовані місця
var BookingMember = function(booking){
	this.booking = booking;
	// Створює рядок зарезервованого місця
	this.createTr = function(booking){
		for(var i in booking){
				var el = $('#selectSeatBooked table tbody').find('tr[data-seat-id="'+ booking[ i ].seatId +'"] ').eq(0);
					
				if(el.length == 0){
					var tr = $('<tr data-seat-id="'+booking[ i ].seatId+'"></tr>');
					
						tr.append($('<td>'+booking[ i ].stadium+'</td>'))
						.append($('<td>'+booking[ i ].sector+'</td>'))
						.append($('<td>'+booking[ i ].row+'</td>'))
						.append($('<td>'+booking[ i ].seat+'</td>'))
						.append($('<td><i class="removeSeatBooked fa fa-times" aria-hidden="true"></i></td>'));
					
					$('#selectSeatBooked table tbody').append(tr);
				}
			}
		
	}
	// Запит на сервер за зарезервованими місцями
	this.get = function(booking){
		var callback = function(data){
			self.createTr(data);
			
		}
		
		var callbackError = function(data){
			//alert('error connect');
		}
		
	
		self.booking.loadData('getBookingMember',{},callback,callbackError);
			
	}
	// відмова від бронювання
	this.remove = function(booking){
		if(confirm('Відмовитись від бронювання?')){
			var callback = function(data){
				
				for(var seatId in data.cencelData){
					
					var booking = {
						stadium:0,
						sector:0,
						row:{},
						seat:{seatId:data.cencelData[seatId].seatId},
					}; 
					self.removeSelectSeatBooking(data.cencelData[seatId].seatId);
					self.booking.emitStatusSeat({booking:booking,status:0});
					//TODO; Забрати  в зарезервованих
				}
			}
			
			var callbackError = function(data){
				alert('error connect');
			}
			//console.log(self.selectedSeat);
		
			self.booking.loadData('bookingCencel',{seat:booking},callback,callbackError);
				
		}
	}
	
	this.addBooked = function(count,newSector){
		var countBooked = $('.countBooked');
		if(newSector === true){
			var c = count;
		}else{
			var c = parseInt(countBooked.html())+count;
		}
		 
		countBooked.html(c);
		
	}
	// Зробити, щоб коректно працювало при зміні сектора,і коли знаходиться користувач на іншому секторі,чим той,в якому прйшли зміни
	this.removeBooked = function(count){
		var countBooked = $('.countBooked');
		var c = parseInt(countBooked.html())-count;
		countBooked.html((c >= 0 ? c : 0));
	}
	// Видалити рядок зарезервованого місця
	this.removeSelectSeatBooking = function(seatId){
		//console.log($('#selectSeat table tbody').find('td[data-seat-id="'+ seatId +'"] '));
		
		var el = $('#selectSeatBooked table tbody tr[data-seat-id="'+ seatId +'"]');
		el.hide(200,function(){
			el.remove();
		})
		
	}
	
	// Слухає події пов'язані з зарезервованими місцями
	var listenersJQ = function(){
		$(document).on('click','.removeSeatBooked',function(){
			
			var thet 		= $(this).closest('tr');
			
			var seatId = thet.data().seatId;
			var booking = {
				stadium:0,
				sector:0,
				row:{},
				seat:{seatId:seatId},
			}; 
			var cencel = {};
				cencel[ booking.seat.seatId ] = booking;
			self.remove(cencel);
			
		})
	}
	
	
	listenersJQ();
	
	
	var self = this;
	return this;
}

var BookingSelect = function(booking){
	this.booking = booking;
	
	// Відправляє на сервер обрані місця для підтвердження бронювання
	this.bookingConfirm = function(afterError){
		var callback = function(data){
			console.log(data);
			for(var seatId in data.addData){
				
				var booking = {
					stadium:0,
					sector:0,
					row:{},
					seat:{seatId:data.addData[seatId].seatId},
				};
				//console.log(self.selectedSeat);
				 var tableBooking = {}
				 tableBooking[ seatId ] = {
					stadium:self.booking.selectedSeat[ seatId ].stadium,
					sector:self.booking.selectedSeat[ seatId ].sector,
					row:self.booking.selectedSeat[ seatId ].row.row,
					seat:self.booking.selectedSeat[ seatId ].seat.seat,
				}; 
				self.booking.BookingMember.createTr(tableBooking);
				self.booking.emitStatusSeat({booking:booking,status:2});
				
			}
			alert('YES');
		}
		
		var callbackError = function(data){
			if(data.status !== undefined){
				
				var countError = 0;
				for(var seatId in data.data.errorData){
					var booking = {
							stadium:0,
							sector:0,
							row:{},
							seat:{seatId:data.data.errorData[seatId].seatId},
						}; 
					self.booking.emitStatusSeat({booking:booking,status:0});
					countError++;
					
				}
				
				for(var seatId in data.data.addData){
					var booking = {
							stadium:0,
							sector:0,
							row:{},
							seat:{seatId:data.data.addData[seatId].seatId},
						}; 
					self.booking.emitStatusSeat({booking:booking,status:2});
				
				}
				
				var countAllow = 0;
				for(var seatId in data.data.allowData){
					countAllow++;
				}
				
				if(countError > 0 && countAllow > 0){
					//TODO: add plural to count
					if(confirm(countError+' Квитків зарезервована кимось іншим, продовжити бронювання '+countAllow+' квитів?')){
						self.bookingConfirm(true);
					}else if(confirm('Почистити квитки, які залишились')){
						console.log('OK clear booking');
						
						for(var seatId in data.data.allowData){	
							var booking = {
								stadium:0,
								sector:0,
								row:{},
								seat:{seatId:data.allowData[seatId].seatId},
							}; 
							self.booking.emitStatusSeat({booking:booking,status:0});
						}	
						
						
					}
				}else{
					alert('Хтось купив всі вибрані квитки(');
				}
				
			}else{
					alert('error connect');
			}
		
		}
		//console.log(self.selectedSeat);
		
		self.booking.loadData('booking',{seat:self.booking.selectedSeat,afterError:afterError},callback,callbackError);
			
	}
	
	// Видаляє рядок з обраними місцями
	this.removeTr = function(seatId){
		//console.log($('#selectSeat table tbody').find('td[data-seat-id="'+ seatId +'"] '));
		delete self.booking.selectedSeat[ seatId ];
		var el = $('#selectSeat table tbody tr[data-seat-id="'+ seatId +'"]');
		el.hide(200,function(){
			el.remove();
		})
		
	}
	// Створює рядок з обраними місцями
	this.createTr = function(temp){
		console.log(temp);
		for(var seatId in temp){
			var el = $('#selectSeat table tbody').find('tr[data-seat-id="'+ seatId +'"] ').eq(0);
				
			if(el.length == 0){
				var tr = $('<tr data-seat-id="'+seatId+'"></tr>');
				
					tr.append($('<td>'+temp[ seatId ].stadium+'</td>'))
					.append($('<td>'+temp[ seatId ].sector+'</td>'))
					.append($('<td>'+temp[ seatId ].row.row+'</td>'))
					.append($('<td>'+temp[ seatId ].seat.seat+'</td>'))
					.append($('<td><i class="removeSeat fa fa-times" aria-hidden="true"></i></td>'));
				
				$('#selectSeat table tbody').append(tr);
			}
		}
	}
	// Змінює стан місця
	this.chengStatusSeat = function(data){
		var seatId = data.booking.seat.seatId;
		var searchElement = function (remove,add){
			var el = $('#rowAndSeat').find('td[data-seat-id="'+ seatId +'"] ').eq(0);
			
			if(el.length > 0){
				if((el.hasClass('info') && remove.indexOf('info') >= 0) || (el.hasClass('active') && remove.indexOf('active') >= 0))
					self.booking.BookingMember.removeBooked(1);
				el.removeClass(remove).addClass(add);
			}
		}
		switch(data.status){
			case 0://delete
				// видалив останній
				if(data.remove === true ){
					searchElement('warning danger info active','success');
				} 
				// видалив хтось,але не конкретний ,і не останній
				if(data.remove === false && data.my === true){
					searchElement('success warning','danger');
				}
					
				if(self.booking.selectedSeat[ seatId ] !== undefined && data.my === true){
					self.removeTr(seatId);
				}
			break;
			
			case 1:// Вибір місця для бронювання
				
				if(data.my === true){
					searchElement('success danger','warning');
				}else if(self.booking.selectedSeat[ seatId ] === undefined)
					searchElement('success warning','danger');
				
				if(self.booking.selectedSeat[ seatId ] === undefined && data.my === true){
					self.booking.selectedSeat[ seatId ] = data.booking;
					var temp = {};
					temp[ seatId ] = data.booking;
					self.createTr(temp);
				}
			break;
			
			case 2: // Місце заброньоване
				if(data.my === true){
					searchElement('success warning danger','info');
				}else{
					searchElement('success warning danger','active');
				}
				self.booking.BookingMember.addBooked(1,false);
				
				if(self.booking.selectedSeat[ seatId ] !== undefined){
					self.removeTr(seatId);
				}
			break;
			default:
			console.log('wrong status');
			break;
		}
	}
	// Слухає події пов'язані з обраними місцями
	var listenersJQ = function(){
		
		$(document).on('click','.removeSeat',function(){
			var thet 		= $(this).closest('tr');
			
			var seatId = thet.data().seatId;
			var booking = {
				stadium:0,
				sector:0,
				row:{},
				seat:{seatId:seatId},
			}; 
			self.booking.emitStatusSeat({booking:booking,status:0});
			
		})
		
		$(document).on('click','#booking',function(){
			self.bookingConfirm();
		})
		
	}
	
	listenersJQ();
	
	var self = this;
	return this;
}
var Booking = function(){
	//var levelsItem = {1:'#stadiums',2:'#stadiumsSector'}
	this.socket =  new Connection(Broadcast.BROADCAST_URL+":"+Broadcast.BROADCAST_PORT);;
	this.selectedSeat = {};
	this.BookingMember = new BookingMember(this);
	this.BookingSelect = new BookingSelect(this);
	
	this.current = {
		'stadium':0,
		'sector':0,
	};
	// Робить запит на сервер
	this.loadData = function(type,dataSend,callback,callbackError){
		
			$.ajax({
				url: "main/"+type,
				type: "POST", 
				dataType: "json", 
				data: dataSend, 
				error: function(data){callbackError(data)},
				success: function(data){
					if(data.status === true){
						callback(data.data);
					}else{
						callbackError(data)
					}
				}
			});
		
		
	}
	
	// Чистить списки при зміні вхідних параметрів
	this.clearSelect = function(level){
		
		if(level <= 0)
			return false;
		if(level <= 2){
			$('#stadiumsSector').find('option:not(:first)').remove();
			$('#stadiumsSector').parent().find('i').removeClass('fa-check-square fa-exclamation fa-spinner fa-spin').addClass('fa-square');
		}
		if(level <= 3){
			//delete test.blue;
			$('#rowAndSeat').html('');
			//$('#selectSeat').html('');
		}
		
		
		
	}
	//Створює елементи для вибору вхідних даних
	this.createOptions = function(data){
		var str = '';
		if(data.length > 0){
			$.each(data,function(key,val){
				str += '<option value="'+val.id+'">'+val.number+'</option>\n';
			})
		}
		
		return str;
	}
	//Створює сектор з місцями
	this.createSector = function(data,idSector,selectedSeatData){
		self.selectedSeat = $.extend({}, selectedSeatData.my);
		var rowAndSeat = $('#rowAndSeat');
		self.BookingMember.addBooked(data.count,true);
		console.log(data);
		rowAndSeat.html('');
		var table = $('<table data-sector="'+idSector+'"></table>')
					.addClass('table table-bordered table-hover table-condensed');
					//.data({'sector':idSector});
		
		
			for(var i in data.rowAndSeat){
				var row = $('<tr data-row-id="'+data.rowAndSeat[i].id+'" data-row="'+data.rowAndSeat[i].number+'" ></tr>')
							.addClass('row');
							//.data({'row-id':data[i].id,'row':data[i].number});
				
				$('<th></th>')
					.addClass('nameRow')
					.html(data.rowAndSeat[i].number)
					.appendTo(row);
				
				
				for(var j in data.rowAndSeat[i].seat){
					var td = $('<td data-seat-id="'+data.rowAndSeat[i].seat[j].id+'" data-seat="'+data.rowAndSeat[i].seat[j].number+'"></td>')
							.addClass('addSeat');
							if(data.rowAndSeat[i].seat[j].other == 0){
								if(selectedSeatData.all[ data.rowAndSeat[i].seat[j].id ] !== undefined){
									if(self.selectedSeat[ data.rowAndSeat[i].seat[j].id ] !== undefined){
										td.addClass('warning');
									}else{
										td.addClass('danger');
									}
								}else{
									td.addClass('success');
								}
							}else{
								if(data.rowAndSeat[i].seat[j].my == 1){
									td.addClass('info');
								}else{
									td.addClass('active');
								}
							}
							//.data({'seat-id':data[i].seat[j].id,'seat':data[i].seat[j].number})
							
							td.html(data.rowAndSeat[i].seat[j].number)
							.appendTo(row);
				}
				row.appendTo(table);
			}
			//<i class="fa fa-ticket" aria-hidden="true"></i>
			
		 table.appendTo(rowAndSeat);
	}
	// обробляє стан вибору сектору,
	this.chengInputSector = function(selectedSeatData){
		var thet 		= $('#stadiumsSector');
			thet.prop('disabled',false)
		var idSector 	= thet.find('option:selected').val();
		var icon		= thet.parent().find('i');
		if(idSector  <= 0){
			icon.removeClass('fa-check-square fa-exclamation fa-spinner fa-spin').addClass('fa-square');
			self.current.sector = 0;
			thet.prop('disabled',false);
		}else{
			var success = function(data){
				
				self.current.sector = idSector;
				
				self.createSector(data,idSector,selectedSeatData);
				self.BookingSelect.createTr(self.selectedSeat);
				$('#rowAndSectorName span').html(idSector);
				icon.removeClass('fa-spinner fa-spin').addClass('fa-check-square');
			}
			var error = function(data){
				icon.removeClass('fa-spinner fa-spin').addClass('fa-exclamation');
				console.log(data);
			}
			icon.removeClass('fa-check-square fa-exclamation fa-square').addClass('fa-spinner fa-spin');
			self.loadData('getRow',{id:idSector},success,error);
			
		}
	}
	// відправляє дані на сокет
	this.emitStatusSeat = function(seat){
		self.socket.sendMsg('processBooking',seat);
	}
	// Слухачі пов'язані з вибором стадіону, сектору та місця
	var listenersJQ = function(){
		// Зміна стану вибору стадіону
		$(document).on('change','#stadiums',function(){
			self.clearSelect(2);
			
			var thet 	= $(this);
			thet.prop('disabled',true)
			var idStadium 		= thet.find('option:selected').val();
			var icon	= thet.parent().find('i');
			
			if(idStadium  <= 0){
				icon.removeClass('fa-check-square fa-exclamation fa-spinner fa-spin').addClass('fa-square');
				self.current.stadium = 0;
				thet.prop('disabled',false);
			}else{
				var success = function(data){
					thet.prop('disabled',false);
					self.current.stadium = idStadium;
					
					var str = self.createOptions(data);
					$('#stadiumsSector').append(str);
					
					icon.removeClass('fa-spinner fa-spin').addClass('fa-check-square');
					
					//$('#stadiumsSector').val('1').change();
				}
				
				var error = function(data){
					icon.removeClass('fa-spinner fa-spin').addClass('fa-exclamation');
					console.log(data);
				}
				
				icon.removeClass('fa-check-square fa-exclamation fa-square').addClass('fa-spinner fa-spin');
				self.loadData('getSector',{id:idStadium},success,error);
			}
				
			
			
			
		})
		
		// Зміна стану вибору сектору
		$(document).on('change','#stadiumsSector',function(){
			self.clearSelect(3);
			var thet 	= $(this);
			thet.prop('disabled',true)
			self.socket.sendMsg('getProcessBooking',{});
		})
		
		// Додає місце до обраного,якщо воно ще не зарезервовано
		$(document).on('click','.addSeat',function(){
			var thet 		= $(this);
			var row = thet.parent().data();
			var seat = thet.data();
			var booking = {
				stadium:self.current.stadium,
				sector:self.current.sector,
				row:row,
				seat:seat,
			}; 
			
			if(!thet.hasClass('active') && !thet.hasClass('info')){
				if(self.selectedSeat[booking.seat.seatId] === undefined )
					self.emitStatusSeat({booking:booking,status:1});
				else
					self.emitStatusSeat({booking:booking,status:0}); 
			}else if(thet.hasClass('info')){
				var cencel = {};
				cencel[ booking.seat.seatId ] = booking;
				self.BookingMember.remove(cencel);
			}
		})
	}
	var listenersSW = function(){
		// обробляє дані обраних місці від сокету при завантажені
		$(window).on('getProcessBooking', function (e) {
			var data = e.state;
			self.chengInputSector(data);
		})
		// обробляє дані обраних місці від сокету
		$(window).on('processBooking', function (e) {
			var data = e.state;
			
			if(data.status !== undefined){
				self.BookingSelect.chengStatusSeat(data);
			}else
				console.log('not status to cheng element ');
		});
	}
	
	var self = this;
	listenersJQ();
	listenersSW();
	
	return this;
}
var booking;

$(document).ready(function(){
	
	booking = new Booking();
	booking.BookingMember.get();
	$('#stadiums').val('1').change();
	
})