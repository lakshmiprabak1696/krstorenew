function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

$(document).ready(function() {
	//Form Submit for IE Browser
	$('button[type=\'submit\']').on('click', function() {
		$("form[id*='form-']").submit();
	});

	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();

		if (element.hasClass('form-group')) {
			element.addClass('has-error');
		}
	});

	// tooltips on hover
	$('[data-toggle=\'tooltip\']').tooltip({container: 'body', html: true});

	// Makes tooltips work on ajax generated content
	$(document).ajaxStop(function() {
		$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
	});

	// https://github.com/opencart/opencart/issues/2595
	$.event.special.remove = {
		remove: function(o) {
			if (o.handler) {
				o.handler.apply(this, arguments);
			}
		}
	}
	
	// tooltip remove
	$('[data-toggle=\'tooltip\']').on('remove', function() {
		$(this).tooltip('destroy');
	});

	// Tooltip remove fixed
	$(document).on('click', '[data-toggle=\'tooltip\']', function(e) {
		$('body > .tooltip').remove();
	});
	
	$('#button-menu').on('click', function(e) {
		e.preventDefault();
		
		$('#column-left').toggleClass('active');
	});

	// Set last page opened on the menu
	$('#menu a[href]').on('click', function() {
		sessionStorage.setItem('menu', $(this).attr('href'));
	});

	if (!sessionStorage.getItem('menu')) {
		$('#menu #dashboard').addClass('active');
	} else {
		// Sets active and open to selected page in the left column menu.
		$('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parent().addClass('active');
	}
	
	$('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('li > a').removeClass('collapsed');
	
	$('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('ul').addClass('in');
	
	$('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('li').addClass('active');
	
	// Image Manager
	$(document).on('click', 'a[data-toggle=\'image\']', function(e) {
		var $element = $(this);
		var $popover = $element.data('bs.popover'); // element has bs popover?

		e.preventDefault();

		// destroy all image popovers
		$('a[data-toggle="image"]').popover('destroy');

		// remove flickering (do not re-add popover when clicking for removal)
		if ($popover) {
			return;
		}

		$element.popover({
			html: true,
			placement: 'right',
			trigger: 'manual',
			content: function() {
				return '<button type="button" data-toggle="tooltip" title="Image Upload" id="button-upload" class="btn btn-primary"><i class="fa fa-upload"></i></button> <button type="button" id="button-clear" class="btn btn-danger"><i class="fa fa-trash-o"></i></button>';
			}
		});

		$element.popover('show');

                $('#button-upload').on('click', function(event) {
                    
                    
                    var pp = $("#button-upload").parent().parent().attr("id")
                       
						
                        //alert($("#"+pp).next().attr("id"));
                       // alert($("#"+pp).prev().attr("id"));
                                                
                    var img_name_id = $("#"+pp).next().attr("id")  ;
                    var a_href_id = $("#"+pp).prev().attr("id")  ;
                    
                           
                    $('#form-upload').remove();

                    $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" id="img_uploader" name="file[]" value="" multiple="multiple" /></form>');

                    $('#form-upload input[name=\'file[]\']').trigger('click');

                    if (typeof timer != 'undefined') {
                    clearInterval(timer);
                    }
                    $('#button-upload').button('loading');
                    timer = setInterval(function() {
                            if ($('#form-upload input[name=\'file[]\']').val() != '') {
                              
                                    clearInterval(timer);
                                    var helloBtn = document.getElementById("img_uploader");
                                    
                                    var tdate = new Date();
                                    var dd = tdate.getDate(); //yields day
                                    var MM = tdate.getMonth(); //yields month
                                    var yyyy = tdate.getFullYear(); //yields year
                                    var currentDate = dd + "_" + (MM + 1) + "_" + yyyy;
                                    var text = "";
                                    var possible = "ABCDEFGHIkLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                                    for (var j = 0; j < 7; j++) {
                                          text += possible.charAt(Math.floor(Math.random() * possible.length));
                                    }
                                     for (var i = 0; i < helloBtn.files.length; i++)
                                    {
                                          
                                          console.log(helloBtn.files[i].path);
                                          console.log(helloBtn.files[i].path);
                                          var fileName = helloBtn.files[i].name;
                                          helloBtn.files[i].urlPath = currentDate + '/' + text + '/' + fileName; 
                                          file=helloBtn.files[i];
                                          fSize=helloBtn.files[i].size;                         
                                          fileExt = (fileName.substr(fileName.lastIndexOf('.') + 1));
                                          console.log("HHHH ere image file Detals : ");
                                          console.log(helloBtn.files[i] );
                                          console.log("HHHH ere End");
                                          $.getJSON('index.php?route=tool/urlsign/url&user_token=' + getURLVar('user_token')+'&verb=PUT&objName=' + helloBtn.files[i].urlPath + '&content_type=' + encodeURIComponent(file.type), function (data) {
                                            var xhr = createCORSRequest('PUT', data.data.url)
                                            xhr.onload = function () {
                                                      if (xhr.status === 200) {
                                                            //alert("YaY!");
                                                            console.log("success");
                                                            readURL(helloBtn, currentDate + '/' + text + '/' + fileName,a_href_id,img_name_id);
                                                            $('#button-upload').button('reset');
                                                            $('#'+a_href_id).trigger('click');
                                                      } else {
                                                            console.log("failure");
                                                            //alert('failure')
                                                      }
                                                }
                                                xhr.onerror = function () {
                                                      console.log("failure");
                                                }
                                                xhr.setRequestHeader('Content-Type', ''); 
                                                xhr.send(file);
                                          })
                                          
                                    }
                                     
                            }
                    }, 500);
            });
            
             function createCORSRequest(method, url) {
                    var xhr = new XMLHttpRequest()

                    if ('withCredentials' in xhr) {
                          xhr.open(method, url, true)
                    } else if (typeof XDomainRequest !== 'undefined') {
                          xhr = new XDomainRequest()
                          xhr.open(method, url)
                    } else {
                          xhr = null
                    }
                    return xhr
              }


              function readURL(input,img_val,a_href_id,img_name_id) {
                if (input.files && input.files[0]) {
                  var reader = new FileReader();
                  reader.onload = function(e) {
                      $('#'+a_href_id).find('img').attr('height', 100);   
                      $('#'+a_href_id).find('img').attr('width', 100);   
                      $('#'+a_href_id).find('img').attr('src',  e.target.result);                    
                      $('#'+a_href_id).find('img').attr('value',  img_val);  
                      
                      $('#'+img_name_id).attr('value',  img_val); 
                      
                      

                  }

                  reader.readAsDataURL(input.files[0]); // convert to base64 string
                }
              }

              //$("#img_uploader").change(function() {
                //readURL(this);
             // });

		$('#button-image').on('click', function() {
			var $button = $(this);
			var $icon   = $button.find('> i');

			$('#modal-image').remove();

			$.ajax({
				url: 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token') + '&target=' + $element.parent().find('input').attr('id') + '&thumb=' + $element.attr('id'),
				dataType: 'html',
				beforeSend: function() {
					$button.prop('disabled', true);
					if ($icon.length) {
						$icon.attr('class', 'fa fa-circle-o-notch fa-spin');
					}
				},
				complete: function() {
					$button.prop('disabled', false);

					if ($icon.length) {
						$icon.attr('class', 'fa fa-pencil');
					}
				},
				success: function(html) {
					$('body').append('<div id="modal-image" class="modal">' + html + '</div>');

					$('#modal-image').modal('show');
				}
			});

			$element.popover('destroy');
		});

		$('#button-clear').on('click', function() {
			$element.find('img').attr('src', $element.find('img').attr('data-placeholder'));

			$element.parent().find('input').val('');

			$element.popover('destroy');
		});
	});
});

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			var $this = $(this);
			var $dropdown = $('<ul class="dropdown-menu" />');

			this.timer = null;
			this.items = [];

			$.extend(this, option);

			$this.attr('autocomplete', 'off');

			// Focus
			$this.on('focus', function() {
				this.request();
			});

			// Blur
			$this.on('blur', function() {
				setTimeout(function(object) {
					object.hide();
				}, 200, this);
			});

			// Keydown
			$this.on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Click
			this.click = function(event) {
				event.preventDefault();

				var value = $(event.target).parent().attr('data-value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}
			}

			// Show
			this.show = function() {
				var pos = $this.position();

				$dropdown.css({
					top: pos.top + $this.outerHeight(),
					left: pos.left
				});

				$dropdown.show();
			}

			// Hide
			this.hide = function() {
				$dropdown.hide();
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 200, this);
			}

			// Response
			this.response = function(json) {
				var html = '';
				var category = {};
				var name;
				var i = 0, j = 0;

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						// update element items
						this.items[json[i]['value']] = json[i];

						if (!json[i]['category']) {
							// ungrouped items
							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
						} else {
							// grouped items
							name = json[i]['category'];
							if (!category[name]) {
								category[name] = [];
							}

							category[name].push(json[i]);
						}
					}

					for (name in category) {
						html += '<li class="dropdown-header">' + name + '</li>';

						for (j = 0; j < category[name].length; j++) {
							html += '<li data-value="' + category[name][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[name][j]['label'] + '</a></li>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$dropdown.html(html);
			}

			$dropdown.on('click', '> li > a', $.proxy(this.click, this));
			$this.after($dropdown);
		});
	}
})(window.jQuery);
