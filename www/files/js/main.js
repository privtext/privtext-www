$(document).ready(function() {
    // Toggle hidden description
    (function() {
        var btn = $('.js-toggleHiddenDescription'),
            hiddenBlock = $('.hiddenDescrition');
        
        btn.on('click', function(e) {
            e.preventDefault();
            
            hiddenBlock.slideToggle('fast');
        });
    })();
    
    // Show Settings
    (function() {
        var btn = $('.js-showSettings'),
            hiddenSettings = $('.wrapLeftSettings'),
            btnClose = $('.js-hideSettings'),
            doc = $('body'),
            underBg = $('.underBg');
        
        btn.on('click', function(e) {
            e.preventDefault();
            
            hiddenSettings.animate({
                'left': '0'
            },500, function() {
                hiddenSettings.addClass('opened').removeAttr('style');
            });

            showLeftSettings(this);

			doc.animate({
                'margin-left': '320px'
            }, 500, function() {
                doc.addClass('slideToRight').removeAttr('style');
            });

            if ($(window).width() < 1024) {
                underBg.stop().fadeIn('fast', function() {
                    underBg.addClass('showed').removeAttr('style');
                });
            }

            if ($(this).closest('.mainHeader').length == 0) {
                $(this).stop().fadeOut('fast');
            }

            setCookie('openedLeft', 1, 72000);
        });
        
        btnClose.on('click', function(e) {
            e.preventDefault();

            hideLeftSettings();
        });
    })();

    // Scroll to top in create
    function onScrollCreateButton(){
        window.scrollTo(0, 0);
        $('.js-showSettings').hide();
        hideLeftSettings();
        if ($('.resultContentSettings.notEmpty').length > 0) {
            $('.js-showResultSettings.showed').addClass('disabled');
        }
    }
    (function(){
        $('#createButton').on('click', function(){
            // window.scrollTo(0, 0);
            // $('.js-showSettings').hide();
            // hideLeftSettings();
            // if ($('.resultContentSettings.notEmpty').length > 0) {
            //     $('.js-showResultSettings.showed').addClass('disabled');
            // }

            /*pass*/
        });
    })();
    
    // Show Menu Mobile
    (function() {
        var btnOpenMobileMenu = $('.js-showMobileMenu'),
            headerTop = $('.mainHeader'),
            mobileMenu = $('.mainHeader nav'),
            doc = $('body'),
            underBg = $('.underBg'),
            hideMenu = $('.js-hideMobileMenu');
        
        btnOpenMobileMenu.on('click', function(e) {
            e.preventDefault();
            
            underBg.stop().fadeIn('fast', function() {
                underBg.addClass('showed').removeAttr('style');
            });
            mobileMenu.animate({
                'right': '0'
            }, 500, function() {
                mobileMenu.addClass('openedMenu').removeAttr('style');
            });
            doc.animate({
                'right': '320px'
            }, 500, function() {
                doc.addClass('slideToLeft').removeAttr('style');
            });
            headerTop.animate({
                'right': '320px'
            }, 500);
        });
        
        hideMenu.on('click', function(e) {
            e.preventDefault();
            
            underBg.stop().fadeOut('fast', function() {
                underBg.removeClass('showed').removeAttr('style');
            });
            mobileMenu.animate({
                'right': '-320px'
            }, 500, function() {
                mobileMenu.removeClass('openedMenu').removeAttr('style');
            });
            doc.animate({
                'right': '0'
            }, 500, function() {
                doc.removeClass('slideToLeft').removeAttr('style');
            });
            headerTop.animate({
                'right': '0'
            }, 500, function() {
                headerTop.removeAttr('style');
            });
        });
    })();
    
    
    // Click to background
    (function() {
        var hiddenSettings = $('.wrapLeftSettings'),
            underBg = $('.underBg'),
            btnShowSettings = $('.js-showSettings'),
            headerTop = $('.mainHeader'),
            mobileMenu = $('.mainHeader nav'),
            doc = $('body');
        
        underBg.on('click', function() {
            if (hiddenSettings.hasClass('opened')) {
                hiddenSettings.animate({
                    'left': '-320px'
                }, 500, function() {
                    hiddenSettings.removeClass('opened').removeAttr('style');
                });
                $(this).stop().hide();
                $(this).removeClass('showed').removeAttr('style');
                doc.animate({
                    'margin-left': '0'
                }, 500, function() {
                    doc.removeClass('slideToRight').removeAttr('style');
                });
                if ($(window).width() > 767) {
                    btnShowSettings.stop().fadeIn('fast', function() {
                        btnShowSettings.removeAttr('style');
                    });
                }

                $('html').removeAttr('style');

                setCookie('openedLeft', 0, 72000);
            }
            if (mobileMenu.hasClass('openedMenu')) {
                mobileMenu.animate({
                    'right': '-320px'
                }, 500, function() {
                    mobileMenu.removeClass('openedMenu').removeAttr('style');
                });
                $(this).stop().hide();
                $(this).removeClass('showed').removeAttr('style');
                doc.animate({
                    'right': '0'
                }, 500, function() {
                    doc.removeClass('slideToLeft').removeAttr('style');
                });
                headerTop.animate({
                    'right': '0'
                }, 500, function() {
                    headerTop.removeAttr('style');
                })
            }

            closePopup();
        });
    })();

    // Focus Textarea
    (function(){
        var textarea = $('#browser-view, #privdata');
        var title = textarea.parent().siblings('.titleForm');
        if (screen.width < 768) {
            textarea.on('focus', function(){
                $(this).css({
                    'z-index':'5',
                    'position':'relative'
                });
                title.css({
                    'z-index': '5',
                    'position': 'relative'
                });
            });
            textarea.on('blur', function(){
                $(this).removeAttr('style');
                title.removeAttr('style');
            });
        }

    })();
    
    // Reset styles on resize
    $(window).on('resize', function() {
        var doc = $('body'),
            btnShowSettings = $('.managerForm .js-showSettings'),
            hiddenSettings = $('.wrapLeftSettings'),
            headerTop = $('.mainHeader'),
            mobileMenu = $('.mainHeader nav'),
            underBg = $('.underBg');

        if ($(window).width() <= 1024 && (hiddenSettings.hasClass('opened') || mobileMenu.hasClass('openedMenu'))) {
            underBg.stop().fadeIn('fast', function() {
                underBg.addClass('showed').removeAttr('style');
            });
        }
        else {
            if(!$('.popupWrapper').is(':visible')){
                underBg.stop().fadeOut('fast', function() {
                    underBg.removeClass('showed').removeAttr('style');
                });
            }else{
                $('.popupWrapper').each(function(){
                    if($(this).is(':visible')){
                        var popup = $(this);
                        popup.css({
                            'margin-top': popup.height()/(-2),
                            'margin-left': popup.width()/(-2)
                        });
                        return false;
                    }
                })
            }
        }

        if ($(window).width() < 768) {
            btnShowSettings.removeAttr('style');
        }

        if (hiddenSettings.hasClass('opened')) {
            btnShowSettings.hide();
        }
        else {
            if ($(window).width() > 767) {
                btnShowSettings.show();
            }
        }

        if ($(window).width() > 767) {
            if (mobileMenu.hasClass('openedMenu')) {
                mobileMenu.removeClass('openedMenu');
                underBg.stop().fadeOut('fast', function() {
                    underBg.removeClass('showed').removeAttr('style');
                });
                doc.removeClass('slideToLeft');
                headerTop.removeAttr('style');
            }
            $('.btnReply').css('display', 'inline-block');
        }
    });
    
    // Copy to buffer    
    (function() {
        
        var deleteNoteButton = document.querySelector('.button-delete-message-now');
        deleteNoteButton && deleteNoteButton.addEventListener('click', function(e){
          e.preventDefault();
          var noteid = $('.button-delete-message-now').attr('data-note');
          if(noteid){ widgetPrivText.deleteNoneById(noteid); }
        });
        
    })();

    // Tabs
    (function() {
        $('.js-tabLink').on('click', function(e) {
            e.preventDefault();
            var allLinksHeader = $(this).closest('.nav-tabs').find('li'),
                currentLink = $(this).closest('li'),
                allContents = $(this).closest('.nav-tabs').siblings('.tab-content').find('.tab-pane'),
                currentTabContent = $('#' + $(this).attr('href').slice(1));

            allLinksHeader.removeClass('active');
            currentLink.addClass('active');
            allContents.removeClass('active');
            currentTabContent.addClass('active');
        });
    })();

    // Confirm Popup
    $('.js-confirmNowPopup').on('click', function(e) {
        e.preventDefault();
        var popup = $('.js-popupConfirm');

        (!widgetPrivText.read_note()
        &&
        popup.stop().fadeOut('fast', function() {
            openPopupPrompt();
        }))
        || closePopup();
    });

    // Enter Popup
    $('.js-enterPopup').on('click', function(e) {
        //var popup = $('.js-popupPrompt');

        e.preventDefault();
        widgetPass();
        // widgetPrivText.read_note($('.js-popupPrompt input[name="note_private_password"]').val());
        // widgetPrivText.read_note($('.js-popupPrompt input[name="note_private_password"]').val()) && closePopup();
    });

    $('input[name="note_private_password"]').keydown(function(e) {
        // e.preventDefault();
        if (e.keyCode == 13) {
            widgetPass();
            return false;
        }
    });

    // Close Popup
    $('.js-closePopup').on('click', function(e) {
        e.preventDefault();
        closePopup();
    });

    // Change value settings
    (function(){
        var fieldSettings = $('.blockLeftSettings input[type="text"], .blockLeftSettings input[type="password"], .blockLeftSettings select');
        fieldSettings.on('change', showSettings);
    })();

    // Select item setting
    (function(){
        var currentSetting = $('.resultContentSettings span');
        currentSetting.on('click', function(){selectItemSettings($(this).attr('class').slice(7))});
    })();

    // Reset item setting
    (function(){
        $('body').on('click', '.js-removeCurrentSettings', function(e){
            e.preventDefault();
            resetItemSetting(this, $(this).siblings('span').attr('class').slice(7));

            if ($(this).siblings('.js-showtimelive').length > 0) {
                describeSelected('#timelive option:selected');
            }            
        });
    })();

    // Show all settings in start
    showSettingInStart();

    if ($('textarea').length == 0 || $('textarea').attr('readonly') || $('#contactform-body').length > 0) {
        $('.js-showSettings').hide();
    }
    
});

// Show left settings
function showLeftSettings(el) {
    var el = $(el).attr('class');
    var hiddenSettings = $('.wrapLeftSettings'),
        doc = $('body'),
        underBg = $('.underBg');

    hiddenSettings.animate({
        'left': '0'
    },500, function() {
        hiddenSettings.addClass('opened').removeAttr('style');
    });

    doc.animate({
        'margin-left': '320px'
    }, 500, function() {
        doc.addClass('slideToRight').removeAttr('style');
    });

    if ($(window).width() <= 1024) {
        underBg.stop().fadeIn('fast', function() {
            underBg.addClass('showed').removeAttr('style');
        });
    }

    if ($(el).closest('.mainHeader').length == 0) {
        $(el).stop().fadeOut('fast');
    }

    setCookie('openedLeft', 1, 72000);
}

function hideLeftSettings() {

    var btn = $('.js-showSettings'),
        hiddenSettings = $('.wrapLeftSettings'),
        doc = $('body'),
        underBg = $('.underBg');

    hiddenSettings.animate({
        'left': '-320px'
    }, 500, function() {
        hiddenSettings.removeClass('opened').removeAttr('style');
    });

    doc.animate({
        'margin-left': '0'
    }, 500, function() {
        doc.removeClass('slideToRight').removeAttr('style');
    });

    underBg.stop().fadeOut('fast', function() {
        underBg.removeClass('showed').removeAttr('style');
    });
    
    if ($(window).width() > 767) {
        btn.stop().fadeIn('fast');
    }

    setCookie('openedLeft', 0, 72000);
}

// Show all settings in start
function showSettingInStart() {
    var fields = $('.js-itemLeftSettings');
    var mainBlock = $('.resultContentSettings');
    var flag = false;
    
    fields.each(function() {
        if (this.tagName == 'SELECT') {
            if ($(this).val() != 0) {
                flag = true;
                var klass = '.js-show' + $(this).attr('id');
                $(klass).closest('.js-showResultSettings').addClass('showed').removeClass('disabled').find('span').text($(this).find('option:selected').text());
            }
        } else {
            if ($(this).val() != '') {
                flag = true;
                var klass = '.js-show' + $(this).attr('id');

                var startText = ($(this).attr('type') == 'password') ? 'Password is entering' : $(this).val();

                $(klass).closest('.js-showResultSettings').addClass('showed').removeClass('disabled').find('span').text(startText);
            }
        }
    });

    if (flag) {
        mainBlock.addClass('notEmpty');
    }

    $(document).on('focus', 'textarea[placeholder="Your result link"]', function() {
        $(this).select();
    });
}

// Show settings in index page
function showSettings() {
    var curId = $(this).attr('id');
    var resultContentSettings = $('.resultContentSettings');
    var currentFieldText = null;
    var currentItemContent = $('.js-show' + curId);
    var currentItemContentWrapper = currentItemContent.closest('.js-showResultSettings');

    if (this.nodeName == 'SELECT') {
        if ($(this).val() != 0) {
            currentFieldText = $(this).find('option:selected').text();
            currentItemContentWrapper.addClass('showed');
            currentItemContent.text(currentFieldText);
        } else {
            currentItemContentWrapper.removeClass('showed');
            currentItemContent.text('');
        }
    } else {
        currentFieldText = $(this).val();
        if (currentFieldText != '') {
            currentItemContentWrapper.addClass('showed');

            if ($(this).attr('type') == 'password') {
                currentFieldText = 'Password is entering';
            }
            currentItemContent.text(currentFieldText);
        } else {
            currentItemContentWrapper.removeClass('showed');
            currentItemContent.text('');
        }
    }

    if ($('.js-showResultSettings.showed').length > 0) {
        resultContentSettings.addClass('notEmpty');
    } else {
        resultContentSettings.removeClass('notEmpty');
    }
}

// Focus on selected setting
function selectItemSettings(id) {
    var leftSettings = $('.wrapLeftSettings');
    var btnShowSettings = $('.js-showSettings');

    if (!leftSettings.hasClass('opened') && $('.js-showResultSettings.disabled').length == 0) {
        showLeftSettings(btnShowSettings);
        if (screen.width > 767) {
            btnShowSettings.fadeOut('fast');
        }

        $('#' + id).focus();
    }
}

// Reset selected settings
function resetItemSetting(btn, id) {
    var currentValue = $(btn).siblings('span');
    var wrapper = $(btn).closest('.js-showResultSettings');
    var mainBlock = $('.resultContentSettings');

    if (document.getElementById(id).nodeName == 'SELECT') {
        $('#' + id + ' option').attr('selected', 'selected');
        $('#' + id + ' option').removeAttr('selected');
    } else {
        $('#' + id).val('');
    }
    currentValue.text('');
    wrapper.removeClass('showed');

    if ($('.js-showResultSettings:visible').length == 0) {
        mainBlock.removeClass('notEmpty');
    }
}


function closePopup(p) {
    var popup = (p && $(p)) || $('.popupWrapper'),
        bgPopup = $('.underBg');

    popup.stop().fadeOut('fast');
    bgPopup.stop().fadeOut('fast', function() {
        bgPopup.removeAttr('style');
        popup.removeAttr('style');
    });

    if(popup.hasClass('js-popupQRCode')){
        $('.js-popupQRCode').find('.qrcode-canvas').html('');
    }
}

function openPopupConfirm() {
    var popup = $('.js-popupConfirm'),
        bgPopup = $('.underBg'),
        ml = popup.width()/(-2),
        mt = popup.height()/(-2);

    popup.css({
        'margin-top': mt,
        'margin-left': ml
    });

    // Open Popup
    bgPopup.stop().fadeIn('fast', function() {
        popup.stop().fadeIn('fast');
    });

    /*function closePopupWithNext() {
        popup.stop().fadeOut('fast', function() {
            bgPopup.stop().fadeOut('fast', function() {
                bgPopup.removeAttr('style');
                popup.removeAttr('style');
            });
        });
    }*/
}


function openPopupErrorMaxSize() {
    var popup = $('.js-popupErrorMaxSize'),
        bgPopup = $('.underBg'),
        ml = popup.width()/(-2),
        mt = popup.height()/(-2);

    popup.css({
        'margin-top': mt,
        'margin-left': ml
    });

    // Open Popup
    bgPopup.stop().fadeIn('fast', function() {
        popup.stop().fadeIn('fast');
    });
}


function openPopupPrompt(err) {
    var popup = $('.js-popupPrompt'),
        bgPopup = $('.underBg'),
        ml = popup.width()/(-2),
        mt = popup.height()/(-2),
        descError = popup.find('.describeError');

    popup.css({
        'margin-top': mt,
        'margin-left': ml
    });

    if(err) {
        descError.show();
    } else {
        descError.removeAttr('style');
    }

    // Open Popup
    bgPopup.stop().fadeIn('fast', function() {
        popup.stop().fadeIn('fast');
    });
}

function showLoaderPrompt(flag) {
    var loader = $('.js-popupPrompt .loadingPopup');
    if (flag) {
        loader.removeAttr('style');
    } else {
        loader.show();
    }
}

function setCookie(name, value, options) {
  options = options || {};

  var expires = options.expires;

  if (typeof expires == "number" && expires) {
    var d = new Date();
    d.setTime(d.getTime() + expires * 1000);
    expires = options.expires = d;
  }
  if (expires && expires.toUTCString) {
    options.expires = expires.toUTCString();
  }

  value = encodeURIComponent(value);

  var updatedCookie = name + "=" + value;

  for (var propName in options) {
    updatedCookie += "; " + propName;
    var propValue = options[propName];
    if (propValue !== true) {
      updatedCookie += "=" + propValue;
    }
  }

  document.cookie = updatedCookie;
}

function widgetPass() {
    widgetPrivText.read_note($('.js-popupPrompt input[name="note_private_password"]').val());
}

function describeSelected(el) {
    $('.note-delete-message').hide();
    $('.note-delete-message-'+$(el).val()).show();
}
