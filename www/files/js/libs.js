var defined = function(v){ return !(typeof(v) == 'undefined'); };

var widgetNoteTimeLiveTimer = {
  /* private */
  __timer_interval_id: null,
  __seconds: null,
  __timelive: null,
  __destroyed: null,
  
  stop: function(seconds){
    var self = this;
    self.__seconds = null;
    self.__timelive = null;
    self.__destroyed = true;
    clearInterval(self.__timer_interval_id);
    self.__timer_interval_id = null;
    
    if(widgetPrivText.iswaiting_password()){
      widgetPrivText.allStopAndClear()
    }
  },
  
  isruning: function(){
    return self.__timer_interval_id !== null;
  },
  
  isdestroyed: function(){
    return self.__destroyed === true;
  },
  
  start: function(timelive, seconds){
    var self = this;
    self.__seconds = seconds;
    self.__timelive = timelive;
    self.__destroyed = null;
    
    if(self.__timelive){
      self.update();
      setTimeout(
        function(){
          self.__timer_interval_id = setInterval(
                      function(){
                        self.tick();
                      }, 1000
                    );
        }, 10
      );
    }else if(self.__timelive === null && self.__timelive !== false){
      $('#timelive0').show();
    }
  },
  
  tick: function(){
    var self = this;
    self.__seconds --;
    self.update();
    if(self.__seconds < 1){
      self.stop();
    }
  },
  
  update: function(){
    var self = this;
    var minutes = parseInt(self.__seconds/60);
    var seconds = self.__seconds - (minutes * 60);
    if(minutes + seconds > 0){
      $('#timelive1').show();
      $('#note_delete_minutes').html(minutes);
      $('#note_delete_seconds').html(seconds);
      if(typeof($('.button-delete-message-now').attr('data-note')) != 'undefined'){ $('.button-delete-message-now').show(); }
    }else{
      $('#timelive1').hide();
      $('#timelive0').hide();
      $('.note-delete-message').hide();
      $('.note-delete-message-now').show();
      $('.button-delete-message-now').hide();
    }
  }
};

var widgetPrivText = {

  /* publick */
  MODE_ENCODE: 'encode', /* CONST */
  MODE_DECODE: 'decode', /* CONST */

  /* private */
  
  ajax_success_flag: 0,
  
  ___note: null,
  ___noteid: null,
  ___timelive: null,
  ___timelive_seconds_to_end: null,
  
  __mode: null,
  __proof: null,
  __proof_timer_startedAt: null,
  __proofRecalcInterval: 300000,
  __proofTimeout: 100,
  __proof_interval: null,
  
  __url_get_proof: '/api/proof',
  __url_set_encrypted: '/api/note',
  
  __text_selector: null,
  __browser_link_selector: null,
  __button_selector_start: null,
  
  //may set as option
  _switch_selector_start: null,                       // jquery selector for each tag, what should be hide after result request
  _switch_selector_result: null,                      // jquery selector for each tag, what should be show after result request
  _note_result_handler: function(data){ return 0; },  // method get value as object type, from ajax request, as result
  _loging_handler: function(load){return 0; },        // method get values: `true` - start load or `false` - finish load
  _proof_result_handler: function(){ return 0; },     // method may call after get proof password
  _proof_result_time_selector: null,                 // jquery selector
  
  _blockScreen: {  // object of screen loader
    show: function(){ return 0; },
    hide: function(){ return 0; }
  },
  
  init: function(mode, text_selector, browser_link_selector, button_selector_start){
    this.__mode = mode;                                   // mode for work this widget
    this.__text_selector = text_selector;                 // jquery selector for input/output text, that has been processed
    this.__browser_link_selector = browser_link_selector;
    this.__button_selector_start = button_selector_start; // jquery selector of clickable tag, what must start process 
    
    this.__proof_timer_startedAt = new Date().getTime();
  },
  
  iswaiting_password: function(){
    var self = this;
    return self.___noteid !== null;
  },
  
  deleteNoneById: function(noteid, donothide){
    var self = this;
    var ajax_handler = self.__create_ajax_handler(
      'delete_note', // action
      {noteid: noteid}, // data
      function(data) { // success action
        if(data.ajax_status == 'ok'){
        	if(!donothide){
		        $('.presented-note').hide();
		        $('.empty-note').show();
          }
        }
      }, 
      self.__url_set_encrypted // url request
    );
    self.__makeAjaxQuery(ajax_handler);  
  },
  
  allStopAndClear: function(){
    var self = this;
    self.___note = null;
    self.___noteid = null;
    self.___timelive = null;
    self.___timelive_seconds_to_end = null;
    $('.presented-note').hide();
    $('.empty-note').show();
    $('.js-popupPrompt').stop().fadeOut('fast');
    $('.underBg').stop().fadeOut('fast');
  },
  
  setopt: function(name, value){
    if ('_'+name in this){ this['_'+name] = value; }
  },
  
  start: function(){
    var self = this;
    self.___note = null;
    self.___noteid = null;
    self.___timelive = null;
    self.___timelive_seconds_to_end = null;

    if($('.presented-note').css('display') == 'none'){
    	if(self.__mode == self.MODE_DECODE){
    		return 0;
    	}
    }
    
    self.__wait_active_proof(function(){ self.__start(); });
  },
  
  run:  function(mode){
    var self = this;
    self.___note = null;
    self.___noteid = null;
    self.___timelive = null;
    self.___timelive_seconds_to_end = null;
    self.__activate_proof_password();
    if(self.__button_selector_start){
      $(self.__button_selector_start).click(function(){ self.start(); });
    }
  },
  
  __wait_active_proof: function(cb, counts){
    var self = this;
    if(!defined(counts)){ counts = 0; }
    counts ++;
    if(self.__proof == null || self.__proof == false){
      setTimeout(function(){ self.__wait_active_proof(cb, counts) }, 100);
    }else{
      if(defined(cb)){ cb(); }
    }
  },
  
  __start: function(){
    var self = this;
    switch(self.__mode){
      case self.MODE_ENCODE:
        self.__create_note();
        break;
      case self.MODE_DECODE:
        openPopupConfirm();

        (function(){
          setTimeout(
            function(){
              if(self.__mode == self.MODE_DECODE){
                var noteid = document.location.pathname.split('/').slice(-1).pop().replace('.html', '');
                var ajax_handler = self.__create_ajax_handler2(
                        'read_notedata',
                        {'noteid': noteid, 'async':true}, // data
                        function(data) {
                          if(data.ajax_status == 'ok'){
                            // pass
                          }
                        },
                        self.__url_set_encrypted // url request
                      );
                self.__makeAjaxQuery(ajax_handler);
              }
            }, 100);
        })();

        break;
    }
  },
  
  __activate_proof_password: function(){
    var self = this;
    self.__proof = null;
    if (self.__proof_interval != null){
      clearInterval(self.__proof_interval);
      self.__proof_interval = null;
    }
    var ajax_handler = self.__create_ajax_handler(
      'get_proof_password', // action
      {}, // data
      function(data) { // success action
        self.__proof_interval = setInterval(function(){ self.__miner(data.prefix, data.target); }, self.__proofRecalcInterval);
        setTimeout(function(){ self.__miner(data.prefix, data.target); }, self.__proofTimeout)
      }, 
      self.__url_get_proof // url request
    );
    self.__makeAjaxQuery(ajax_handler);
  },
  
  read_note: function(s_key, note){
    var self = this,
        success = 0;
    if(self.___note){
      var de_note = '';
      showLoaderPrompt(false);
      try{
        de_note = uncipher(s_key, self.___note);
        //send to delete
        if(!self.___timelive){
        	self.deleteNoneById(self.___noteid, true);
        }else if(self.___timelive_seconds_to_end){
		      $('.button-delete-message-now').attr('data-note', self.___noteid);
		      $('.button-delete-message-now').show();
		    }
        self.___note = null;
        self.___noteid = null;
        self.___timelive = null;
        self.___timelive_seconds_to_end = null;
        setTimeout(function(){showLoaderPrompt(true)}, 200);
        closePopup()
        success = !0;
      }catch(err){
        setTimeout(function(){showLoaderPrompt(true)},200);
        openPopupPrompt(true);
        return 0;
      }
      $(self.__text_selector).val(de_note);
      $('.btnReply').show();
      self._note_result_handler({'text':$(self.__text_selector).val()});
    } else{

      return self.__read_note(s_key);
    }
    return success;
  },
  
  __read_note: function(s_key){
    var self = this;
    var noteid = document.location.pathname.split('/').slice(-1).pop().replace('.html', '');
    if(!defined(s_key)){
      s_key = document.location.hash;

      if(s_key == ""){
        openPopupPrompt();
      } else{
        s_key = s_key.substring(1, s_key.length);
      }
    }
    
    if(s_key.length){
      var ajax_handler = self.__create_ajax_handler(
        'read_note', // action
        {'noteid': noteid, 'proof': self.__proof, 'async':false}, // data
        function(data) { // success action
          showLoaderPrompt(false);
          if(data.ajax_status == 'ok'){
            var note = data.note.trim();
            var de_note = '';
            widgetNoteTimeLiveTimer.start(data.timelive, data.timelive_seconds_to_end);
            self.___timelive = data.timelive;
            self.___timelive_seconds_to_end = data.timelive_seconds_to_end;
            self.___noteid = noteid;
            try{
              de_note = uncipher(s_key, note);
              //send to delete
						  if(!self.___timelive){
						  	self.deleteNoneById(self.___noteid, true);
						  }else if(data.timelive_seconds_to_end){
		            $('.button-delete-message-now').attr('data-note', self.___noteid);
		            $('.button-delete-message-now').show();
		          }
              self.___note = null;
              self.___noteid = null;
              self.___timelive = null;
              self.___timelive_seconds_to_end = null;
              setTimeout(function(){showLoaderPrompt(true)}, 200);
              closePopup();
            }catch(err){
              self.___note = note;
              setTimeout(function(){ showLoaderPrompt(true) },200);
              openPopupPrompt(true);
              return 0;
            }
            $(self.__text_selector).val(de_note);
            $('.btnReply').show(0);
            self._note_result_handler({'text':$(self.__text_selector).val()});

            self.ajax_success_flag = !0;
          }else if(data.ajax_status == 'error'){
            if(data.error_type == 'invalid-proof-password'){
              self.__activate_proof_password();
              self.__wait_active_proof(function(){ self.__read_note(s_key); });
              self.ajax_success_flag = 0;
            }else if(data.error_type == 'nodata'){
              $('.empty-note').show();
              $('.presented-note').hide();
              $('.note-delete-message').hide();
              $('.note-delete-message-now').show();
              $('.button-delete-message-now').hide();
              self.ajax_success_flag = !0;
            }else{
              self.ajax_success_flag = 0;
            }
          }
          setTimeout(function(){showLoaderPrompt(true)}, 200);
        }, 
        self.__url_set_encrypted // url request
      );

      self.__makeAjaxQuery(ajax_handler);

      return self.ajax_success_flag;
    }
  },
  
  __create_note: function(){
    var self = this;
    
    var _PAGE_OPENED_LEFT_SETTINGS_BOX = $('.resultContentSettings').hasClass('notEmpty');
    
    var key_length = 16; //_PAGE_OPENED_LEFT_SETTINGS_BOX ? $('#keylength').val() : 32;
    //if(!key_length){ key_length = 32; }
    
    var s_key = _PAGE_OPENED_LEFT_SETTINGS_BOX ? $('#encpasswd').val() : random_string(key_length);
    if(!s_key){ s_key = random_string(key_length); }
    
    var note = self.__cipher(s_key, $(self.__text_selector).val()).trim();

    if(note.length >= 1048576){
      console.log('throw max size limit of encrypted note');
      openPopupErrorMaxSize();
      console.log('openPopupErrorMaxSize');
      return false;
    }

    // onScrollCreateButton();

    window.scrollTo(0, 0);
    $('.js-showSettings').hide();
    hideLeftSettings();
    if ($('.resultContentSettings.notEmpty').length > 0) {
        $('.js-showResultSettings.showed').addClass('disabled');
    }

    var timelive = _PAGE_OPENED_LEFT_SETTINGS_BOX ? $('#timelive').val() : null;
    var noticemail = _PAGE_OPENED_LEFT_SETTINGS_BOX ? $('#noticemail').val() : null;
    
    var ajax_handler = self.__create_ajax_handler(
      'save_note', // action
      {'note': note, 'proof': self.__proof, 'timelive': timelive, 'noticemail':noticemail}, // data
      function(data){ // success action
        if(data.ajax_status == 'ok'){
          var note_url = document.location.protocol+'//'+document.location.hostname+data.url + ((!_PAGE_OPENED_LEFT_SETTINGS_BOX || (_PAGE_OPENED_LEFT_SETTINGS_BOX && !$('#encpasswd').val().length)) ? '#'+s_key : '');
          if(self._switch_selector_start){
            $(self._switch_selector_start).hide();
          }
          if(self._switch_selector_result){
            $(self._switch_selector_result).show();
          }
          $(self.__browser_link_selector).val(note_url);
          self._note_result_handler($.extend(data,{'note_url':note_url}));
          widgetNoteTimeLiveTimer.start(data.timelive, data.timelive_seconds_to_end);
        }else if(data.ajax_status == 'error'){
          if(data.error_type == 'invalid-proof-password'){
            self.__activate_proof_password();
            self.__wait_active_proof(function(){ self.__create_note(); });
          }
        }
      },
      self.__url_set_encrypted // url request
    );
    self.__makeAjaxQuery(ajax_handler);
  },
  
  __cipher: function(s_key, text){
    return cipher(s_key, text);
  },
  
  __miner: function (prefix, target, counter, randpath){
    var self = this;
    if(typeof counter == 'undefined'){
      counter = 0;
      self.__proof_timer_startedAt = new Date().getTime();
      randpath = Math.random();
    }
    started_counter = counter;
    while(true){
      counter++;
      strToHash = prefix + randpath + counter;
      hash = Sha256.hash(strToHash);
      if(hash <= target){
        self.__proof = strToHash;
        self._proof_result_handler(self.__proof);
        self.__proof_update_timer();
        return self.__proof;
      }
      if(counter - started_counter > 10e2){
        setTimeout(function(){ self.__miner(prefix, target, counter, randpath); }, 1);
        return false;
      }
    }
  },
  
  __proof_update_timer: function(){
    var self = this;
    if(self._proof_result_time_selector){
      $(self._proof_result_time_selector).text(((new Date().getTime() - self.__proof_timer_startedAt)/1000) + ' sec');
    }
  },
  
  __makeAjaxQuery: function (i,nohideScreen){
    // return false;
      var self = this;
	  nohideScreen = defined(nohideScreen); 
	  if(!nohideScreen){self._blockScreen.show();}
	  i.data.ajax = 1;
	  if(!defined(i.url)) i.url = window.location.pathname + window.location.search;
	  if(!defined(i.cache)) i.cache = false;
	  if(!defined(i.type)) i.type = 'POST';
	  if(!defined(i.error)) i.error = function (jqXHR, textStatus, errorThrown){
        alert('Connection is lost. Сheck your Internet connection and reload page, please.');
        //alert(errorThrown+textStatus);
        self._blockScreen.hide();
      };
	  $.ajax(i);
  },
  
  __create_ajax_handler: function(action,vals,success,url){
   	  var self = this;
	  if(!defined(vals)) vals = {};
	  var act = {};
	  act.data = {action:action};
	  if(defined(url)){ act.url = url; }
	  if(defined(vals.async)){ act.async = vals.async; }
	  $.each(vals,function(i,v){ act.data[i]=v; });
	  act.success = function (respose){
	    var data = eval ("("+respose+")");
	    if(data.ajax_status=='ok' || data.ajax_status=='error'){
	      self._blockScreen.hide();
	      if(defined(success)){ success(data); }
	    }// else { alert(data.message); } 
	  };
	  return act;
  },

  __create_ajax_handler2: function(action,vals,success,url){
      var self = this;
    if(!defined(vals)) vals = {};
    var act = {};
    act.data = {action:action};
    if(defined(url)){ act.url = url; }
    if(defined(vals.async)){ act.async = vals.async; }
    $.each(vals,function(i,v){ act.data[i]=v; });
    act.success = function (respose){
      var data = eval ("("+respose+")");
      if(data.ajax_status=='ok' || data.ajax_status=='error'){
        if(defined(success)){ success(data); }
      }// else { alert(data.message); } 
    };
    return act;
  }
}
