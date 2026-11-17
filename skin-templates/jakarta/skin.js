// Garden Gnome Software - Skin
// Pano2VR 7.1.12/21036
// Filename: jakarta-skin.ggsk
// Generated 2026-11-12T10:48:41

function pano2vrSkin(player,base) {
	player.addVariable('vis_url_popup', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_sounds_splashscreen', 2, false, { ignoreInState: 1  });
	player.addVariable('vis_skin', 2, false, { ignoreInState: 1  });
	player.addVariable('vis_image_popup', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_pdf_popup', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_video_youtube_popup', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_video_vimeo_popup', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_video_file_popup', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_video_url_popup', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_thumbs', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_info', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_floorplan', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_map', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_image', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_pdf', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_youtube', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_vimeo', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_video_file', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_phone_video_url', 2, false, { ignoreInState: 0  });
	player.addVariable('sounds_splashscreen_accepted', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_nodes', 2, false, { ignoreInState: 0  });
	player.addVariable('vis_thumbnails', 2, false, { ignoreInState: 0  });
	player.addVariable('has_categories', 2, false, { ignoreInState: 0  });
	player.addVariable('id_lokasi', 0, "", { ignoreInState: 0  });
	player.addVariable('status', 0, "", { ignoreInState: 0  });
	player.addVariable('lokasi', 0, "", { ignoreInState: 0  });
	player.addVariable('welcomeText', 0, "", { ignoreInState: 0  });
	player.addVariable('welcomeText_audio', 0, "", { ignoreInState: 0  });
	player.addVariable('drone', 0, "", { ignoreInState: 0  });
	player.addVariable('link_menu', 0, "", { ignoreInState: 0  });
	player.addVariable('link_layanan', 0, "", { ignoreInState: 0  });
	player.addVariable('link_kategori', 0, "", { ignoreInState: 0  });
	player.addVariable('link_informasi', 0, "", { ignoreInState: 0  });
	player.addVariable('link_jelajah', 0, "", { ignoreInState: 0  });
	var me=this;
	var skin=this;
	var flag=false;
	var hotspotTemplates={};
	var skinKeyPressedKey = 0;
	var skinKeyPressedText = '';
	this.player=player;
	player.setApiVersion(7);
	this.player.skinObj=this;
	this.divSkin=player.divSkin;
	this.ggUserdata=player.userdata;
	this.lastSize={ w: -1,h: -1 };
	var basePath="";
	var cssPrefix="";
	// auto detect base path
	if (base=='?') {
		var scripts = document.getElementsByTagName('script');
		for(var i=0;i<scripts.length;i++) {
			var src=scripts[i].src;
			if (src.indexOf('skin.js')>=0) {
				var p=src.lastIndexOf('/');
				if (p>=0) {
					basePath=src.substr(0,p+1);
				}
			}
		}
	} else
	if (base) {
		basePath=base;
	}
	this.elementMouseDown={};
	this.elementMouseOver={};
	var i;
	var hs,el,els,elo,ela,elHorScrollFg,elHorScrollBg,elVertScrollFg,elVertScrollBg,elCornerBg;
	var prefixes='Webkit,Moz,O,ms,Ms'.split(',');
	for(var i=0;i<prefixes.length;i++) {
		if (typeof document.body.style[prefixes[i] + 'Transform'] !== 'undefined') {
			cssPrefix='-' + prefixes[i].toLowerCase() + '-';
		}
	}
	
	player.setMargins(0,0,0,0);
	
	this.updateSize=function(startElement) {
		var stack=[];
		stack.push(startElement);
		while(stack.length>0) {
			var e=stack.pop();
			if (e.ggUpdatePosition) {
				e.ggUpdatePosition();
			}
			if (e.hasChildNodes()) {
				for(var i=0;i<e.childNodes.length;i++) {
					stack.push(e.childNodes[i]);
				}
			}
		}
	}
	
	player.addListener('changenode', function() { me.ggUserdata=player.userdata; });
	
	var parameterToTransform=function(p) {
		return p.def + 'translate(' + p.rx + 'px,' + p.ry + 'px) rotate(' + p.a + 'deg) scale(' + p.sx + ',' + p.sy + ')';
	}
	
	this.findElements=function(id,regex) {
		var r=[];
		var stack=[];
		var pat=new RegExp(id,'');
		stack.push(me.divSkin);
		while(stack.length>0) {
			var e=stack.pop();
			if (regex) {
				if (pat.test(e.ggId)) r.push(e);
			} else {
				if (e.ggId==id) r.push(e);
			}
			if (e.hasChildNodes()) {
				for(var i=0;i<e.childNodes.length;i++) {
					stack.push(e.childNodes[i]);
				}
			}
		}
		return r;
	}
	
	this._=function(text, params) {
		return player._(text, params);
	}
	
	this.languageChanged=function() {
		var stack=[];
		stack.push(me.divSkin);
		while(stack.length>0) {
			var e=stack.pop();
			if (e.ggUpdateText) {
				e.ggUpdateText();
			}
			if (e.ggUpdateAria) {
				e.ggUpdateAria();
			}
			if (e.hasChildNodes()) {
				for(var i=0;i<e.childNodes.length;i++) {
					stack.push(e.childNodes[i]);
				}
			}
		}
	}
	player.addListener('sizechanged', function () { me.updateSize(me.divSkin);});
	player.addListener('languagechanged', this.languageChanged);
	
	this.addSkin=function() {
		var hs='';
		this.ggCurrentTime=new Date().getTime();
		me._variable_vis_sounds_splashscreen = {};
		me._variable_vis_sounds_splashscreen.ggCurrentLogicState = -1;
		me._variable_vis_sounds_splashscreen.logicBlock = function() {
			var newLogicState_vis_sounds_splashscreen;
			if (
				((player.getHasSounds() == true)) && 
				((player.getSoundsPermitted() != 1)) && 
				((player.getVariableValue('sounds_splashscreen_accepted') == false))
			)
			{
				newLogicState_vis_sounds_splashscreen = 0;
			}
			else {
				newLogicState_vis_sounds_splashscreen = -1;
			}
			if (me._variable_vis_sounds_splashscreen.ggCurrentLogicState != newLogicState_vis_sounds_splashscreen) {
				me._variable_vis_sounds_splashscreen.ggCurrentLogicState = newLogicState_vis_sounds_splashscreen;
				if (me._variable_vis_sounds_splashscreen.ggCurrentLogicState == 0) {
					player.setVariableValue('vis_sounds_splashscreen', true);
				}
				else {
					player.setVariableValue('vis_sounds_splashscreen', false);
				}
			}
		}
		me._variable_vis_skin = {};
		me._variable_vis_skin.ggCurrentLogicState = -1;
		me._variable_vis_skin.logicBlock = function() {
			var newLogicState_vis_skin;
			if (
				((player.getVariableValue('vis_url_popup') == false)) && 
				((player.getVariableValue('vis_image_popup') == false)) && 
				((player.getVariableValue('vis_pdf_popup') == false)) && 
				((player.getVariableValue('vis_video_youtube_popup') == false)) && 
				((player.getVariableValue('vis_video_vimeo_popup') == false)) && 
				((player.getVariableValue('vis_video_file_popup') == false)) && 
				((player.getVariableValue('vis_video_url_popup') == false)) && 
				((player.getVariableValue('vis_phone_info') == false)) && 
				((player.getVariableValue('vis_phone_thumbs') == false)) && 
				((player.getVariableValue('vis_phone_map') == false)) && 
				((player.getVariableValue('vis_phone_floorplan') == false)) && 
				((player.getVariableValue('vis_phone_image') == false)) && 
				((player.getVariableValue('vis_phone_pdf') == false)) && 
				((player.getVariableValue('vis_phone_youtube') == false)) && 
				((player.getVariableValue('vis_phone_vimeo') == false)) && 
				((player.getVariableValue('vis_phone_video_file') == false)) && 
				((player.getVariableValue('vis_phone_video_url') == false)) && 
				((player.getVariableValue('vis_sounds_splashscreen') == false))
			)
			{
				newLogicState_vis_skin = 0;
			}
			else {
				newLogicState_vis_skin = -1;
			}
			if (me._variable_vis_skin.ggCurrentLogicState != newLogicState_vis_skin) {
				me._variable_vis_skin.ggCurrentLogicState = newLogicState_vis_skin;
				if (me._variable_vis_skin.ggCurrentLogicState == 0) {
					player.setVariableValue('vis_skin', true);
				}
				else {
					player.setVariableValue('vis_skin', false);
				}
			}
		}
		el=me._hotspot=document.createElement('div');
		el.ggId="hotspot";
		el.ggDx=-0.06;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_container font-jakarta";
		el.ggType='container';
		hs ='';
		hs+='height : 100%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) - 0.06%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='visibility : inherit;';
		hs+='width : 100%;';
		hs+='pointer-events:none;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._hotspot.ggIsActive=function() {
			return false;
		}
		el.ggElementNodeId=function() {
			return player.getCurrentNode();
		}
		me._hotspot.ggUpdatePosition=function (useTransition) {
		}
		me.divSkin.appendChild(me._hotspot);
		player.addListener('activehotspotchanged', function(event) {
			if (hotspotTemplates.hasOwnProperty('hotspot_darat_project')) {
				for(var i = 0; i < hotspotTemplates['hotspot_darat_project'].length; i++) {
					hotspotTemplates['hotspot_darat_project'][i].ggEvent_activehotspotchanged();
				}
			}
			if (hotspotTemplates.hasOwnProperty('door_icon_project')) {
				for(var i = 0; i < hotspotTemplates['door_icon_project'].length; i++) {
					hotspotTemplates['door_icon_project'][i].ggEvent_activehotspotchanged();
				}
			}
			if (hotspotTemplates.hasOwnProperty('box_ui_location_project')) {
				for(var i = 0; i < hotspotTemplates['box_ui_location_project'].length; i++) {
					hotspotTemplates['box_ui_location_project'][i].ggEvent_activehotspotchanged();
				}
			}
			if (hotspotTemplates.hasOwnProperty('drone_icon_project')) {
				for(var i = 0; i < hotspotTemplates['drone_icon_project'].length; i++) {
					hotspotTemplates['drone_icon_project'][i].ggEvent_activehotspotchanged();
				}
			}
			if (hotspotTemplates.hasOwnProperty('box_ui_location_kategori')) {
				for(var i = 0; i < hotspotTemplates['box_ui_location_kategori'].length; i++) {
					hotspotTemplates['box_ui_location_kategori'][i].ggEvent_activehotspotchanged();
				}
			}
			if (hotspotTemplates.hasOwnProperty('360_video_experience_project')) {
				for(var i = 0; i < hotspotTemplates['360_video_experience_project'].length; i++) {
					hotspotTemplates['360_video_experience_project'][i].ggEvent_activehotspotchanged();
				}
			}
			if (hotspotTemplates.hasOwnProperty('hotspot-360-out')) {
				for(var i = 0; i < hotspotTemplates['hotspot-360-out'].length; i++) {
					hotspotTemplates['hotspot-360-out'][i].ggEvent_activehotspotchanged();
				}
			}
		});
		player.addListener('changenode', function(event) {
			if (hotspotTemplates.hasOwnProperty('hotspot_darat_project')) {
				for(var i = 0; i < hotspotTemplates['hotspot_darat_project'].length; i++) {
					hotspotTemplates['hotspot_darat_project'][i].ggEvent_changenode();
				}
			}
			if (hotspotTemplates.hasOwnProperty('door_icon_project')) {
				for(var i = 0; i < hotspotTemplates['door_icon_project'].length; i++) {
					hotspotTemplates['door_icon_project'][i].ggEvent_changenode();
				}
			}
			if (hotspotTemplates.hasOwnProperty('box_ui_location_project')) {
				for(var i = 0; i < hotspotTemplates['box_ui_location_project'].length; i++) {
					hotspotTemplates['box_ui_location_project'][i].ggEvent_changenode();
				}
			}
			if (hotspotTemplates.hasOwnProperty('drone_icon_project')) {
				for(var i = 0; i < hotspotTemplates['drone_icon_project'].length; i++) {
					hotspotTemplates['drone_icon_project'][i].ggEvent_changenode();
				}
			}
			if (hotspotTemplates.hasOwnProperty('box_ui_location_kategori')) {
				for(var i = 0; i < hotspotTemplates['box_ui_location_kategori'].length; i++) {
					hotspotTemplates['box_ui_location_kategori'][i].ggEvent_changenode();
				}
			}
			if (hotspotTemplates.hasOwnProperty('360_video_experience_project')) {
				for(var i = 0; i < hotspotTemplates['360_video_experience_project'].length; i++) {
					hotspotTemplates['360_video_experience_project'][i].ggEvent_changenode();
				}
			}
			if (hotspotTemplates.hasOwnProperty('hotspot-360-out')) {
				for(var i = 0; i < hotspotTemplates['hotspot-360-out'].length; i++) {
					hotspotTemplates['hotspot-360-out'][i].ggEvent_changenode();
				}
			}
			me._variable_vis_sounds_splashscreen.logicBlock();
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('configloaded', function(event) {
			if (hotspotTemplates.hasOwnProperty('hotspot_darat_project')) {
				for(var i = 0; i < hotspotTemplates['hotspot_darat_project'].length; i++) {
					hotspotTemplates['hotspot_darat_project'][i].ggEvent_configloaded();
				}
			}
			if (hotspotTemplates.hasOwnProperty('door_icon_project')) {
				for(var i = 0; i < hotspotTemplates['door_icon_project'].length; i++) {
					hotspotTemplates['door_icon_project'][i].ggEvent_configloaded();
				}
			}
			if (hotspotTemplates.hasOwnProperty('box_ui_location_project')) {
				for(var i = 0; i < hotspotTemplates['box_ui_location_project'].length; i++) {
					hotspotTemplates['box_ui_location_project'][i].ggEvent_configloaded();
				}
			}
			if (hotspotTemplates.hasOwnProperty('drone_icon_project')) {
				for(var i = 0; i < hotspotTemplates['drone_icon_project'].length; i++) {
					hotspotTemplates['drone_icon_project'][i].ggEvent_configloaded();
				}
			}
			if (hotspotTemplates.hasOwnProperty('box_ui_location_kategori')) {
				for(var i = 0; i < hotspotTemplates['box_ui_location_kategori'].length; i++) {
					hotspotTemplates['box_ui_location_kategori'][i].ggEvent_configloaded();
				}
			}
			if (hotspotTemplates.hasOwnProperty('360_video_experience_project')) {
				for(var i = 0; i < hotspotTemplates['360_video_experience_project'].length; i++) {
					hotspotTemplates['360_video_experience_project'][i].ggEvent_configloaded();
				}
			}
			if (hotspotTemplates.hasOwnProperty('hotspot-360-out')) {
				for(var i = 0; i < hotspotTemplates['hotspot-360-out'].length; i++) {
					hotspotTemplates['hotspot-360-out'][i].ggEvent_configloaded();
				}
			}
			me._variable_vis_sounds_splashscreen.logicBlock();
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('soundspermittedchanged', function(event) {
			me._variable_vis_sounds_splashscreen.logicBlock();
		});
		player.addListener('varchanged_sounds_splashscreen_accepted', function(event) {
			me._variable_vis_sounds_splashscreen.logicBlock();
		});
		player.addListener('varchanged_vis_image_popup', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_pdf_popup', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_floorplan', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_image', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_info', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_map', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_pdf', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_thumbs', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_video_file', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_video_url', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_vimeo', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_phone_youtube', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_skin', function(event) {
			if (hotspotTemplates.hasOwnProperty('door_icon_project')) {
				for(var i = 0; i < hotspotTemplates['door_icon_project'].length; i++) {
					hotspotTemplates['door_icon_project'][i].ggEvent_varchanged_vis_skin();
				}
			}
			if (hotspotTemplates.hasOwnProperty('box_ui_location_project')) {
				for(var i = 0; i < hotspotTemplates['box_ui_location_project'].length; i++) {
					hotspotTemplates['box_ui_location_project'][i].ggEvent_varchanged_vis_skin();
				}
			}
			if (hotspotTemplates.hasOwnProperty('box_ui_location_kategori')) {
				for(var i = 0; i < hotspotTemplates['box_ui_location_kategori'].length; i++) {
					hotspotTemplates['box_ui_location_kategori'][i].ggEvent_varchanged_vis_skin();
				}
			}
		});
		player.addListener('varchanged_vis_sounds_splashscreen', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_url_popup', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_video_file_popup', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_video_url_popup', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_video_vimeo_popup', function(event) {
			me._variable_vis_skin.logicBlock();
		});
		player.addListener('varchanged_vis_video_youtube_popup', function(event) {
			me._variable_vis_skin.logicBlock();
		});
	};
	function SkinHotspotClass_hotspot360out(parentScope,hotspot) {
		var me=this;
		var flag=false;
		var hs='';
		me.parentScope=parentScope;
		me.hotspot=hotspot;
		var nodeId=String(hotspot.url);
		nodeId=(nodeId.charAt(0)=='{')?nodeId.substr(1, nodeId.length - 2):''; // }
		me.ggUserdata=skin.player.getNodeUserdata(nodeId);
		me.elementMouseDown={};
		me.elementMouseOver={};
		me.findElements=function(id,regex) {
			return skin.findElements(id,regex);
		}
		el=me._hotspot360out=document.createElement('div');
		el.ggId="hotspot-360-out";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_hotspot ";
		el.ggType='hotspot';
		hs ='';
		hs+='z-index: -1;';
		hs+='cursor : pointer;';
		hs+='height : 0px;';
		hs+='left : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='visibility : inherit;';
		hs+='width : 0px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._hotspot360out.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			if (me.hotspot.url!='' && me.hotspot.url.charAt(0)=='{') { // }
				return me.hotspot.url.substr(1, me.hotspot.url.length - 2);
			} else {
				if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
					return this.parentNode.ggElementNodeId();
				} else {
					return player.getCurrentNode();
				}
			}
		}
		me._hotspot360out.onclick=function (e) {
			player.openNext(player._(me.hotspot.url),player._(me.hotspot.target));
			var list=me.findElements("hotspot-360-out",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			player.triggerEvent('hsproxyclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._hotspot360out.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._hotspot360out.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['hotspot360out']=true;
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._hotspot360out.onmouseleave=function (e) {
			me.elementMouseOver['hotspot360out']=false;
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me._hotspot360out.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_custom_image=document.createElement('div');
		els=me._ht_node_custom_image__img=document.createElement('img');
		els.className='ggskin ggskin_external';
		hs ='';
		hs += 'position: absolute;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.onload=function() {me._ht_node_custom_image.ggUpdatePosition();}
		el.appendChild(els);
		el.ggSubElement = els;
		hs ='';
		el.ggAltText="";
		el.ggScrollbars=false;
		el.ggUpdateText = function() {
			me._ht_node_custom_image.ggSubElement.setAttribute('alt', player._(me._ht_node_custom_image.ggAltText));
			me._ht_node_custom_image.ggUpdateImageTranslation();
		}
		el.ggSetImage = function(img) {
			me._ht_node_custom_image.ggText_untranslated = img;
			me._ht_node_custom_image.ggUpdateImageTranslation();
		}
		el.ggUpdateImage = function() {
			me._ht_node_custom_image.ggSubElement.style.width = '0px';
			me._ht_node_custom_image.ggSubElement.style.height = '0px';
			me._ht_node_custom_image.ggSubElement.src='';
			me._ht_node_custom_image.ggSubElement.src=me._ht_node_custom_image.ggText;
		}
		el.ggUpdateImageTranslation = function() {
			if (me._ht_node_custom_image.ggText != player._(me._ht_node_custom_image.ggText_untranslated)) {
				me._ht_node_custom_image.ggText = player._(me._ht_node_custom_image.ggText_untranslated);
				me._ht_node_custom_image.ggUpdateImage()
			}
		}
		if ((hotspot) && (hotspot.customimage)) {
			el.ggText=el.ggText_untranslated=hotspot.customimage;
			els.setAttribute('src', hotspot.customimage);
			els.style.width=hotspot.customimagewidth + 'px';
			els.style.height=hotspot.customimageheight + 'px';
			me.ggUse3d = hotspot.use3D;
			me.gg3dDistance = hotspot.distance3D;
		}
		els['ondragstart']=function() { return false; };
		player.checkLoaded.push(els);
		el.ggUpdateText();
		el.ggId="ht_node_custom_image";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_external ";
		el.ggType='external';
		hs ='';
		hs+='border : 0px solid #000000;';
		hs+='cursor : pointer;';
		hs+='height : 100%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='visibility : hidden;';
		hs+='width : 100%;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_custom_image.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._ht_node_custom_image.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((me.hotspot.customimage != ""))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._ht_node_custom_image.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._ht_node_custom_image.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._ht_node_custom_image.style.transition='';
				if (me._ht_node_custom_image.ggCurrentLogicStateVisible == 0) {
					me._ht_node_custom_image.style.visibility=(Number(me._ht_node_custom_image.style.opacity)>0||!me._ht_node_custom_image.style.opacity)?'inherit':'hidden';
					me._ht_node_custom_image.ggSubElement.src=me._ht_node_custom_image.ggText;
					me._ht_node_custom_image.ggVisible=true;
				}
				else {
					me._ht_node_custom_image.style.visibility="hidden";
					me._ht_node_custom_image.ggSubElement.src='';
					me._ht_node_custom_image.ggVisible=false;
				}
			}
		}
		me._ht_node_custom_image.logicBlock_visible();
		me._ht_node_custom_image.ggUpdatePosition=function (useTransition) {
			var parentWidth = me._ht_node_custom_image.clientWidth;
			var parentHeight = me._ht_node_custom_image.clientHeight;
			var img = me._ht_node_custom_image__img;
			var aspectRatioDiv = me._ht_node_custom_image.clientWidth / me._ht_node_custom_image.clientHeight;
			var aspectRatioImg = img.naturalWidth / img.naturalHeight;
			var currentWidth,currentHeight;
			if ((hotspot) && (hotspot.customimage)) {
				currentWidth  = hotspot.customimagewidth;
				currentHeight = hotspot.customimageheight;
			}
			if (!me._ht_node_custom_image.ggScrollbars || currentWidth < me._ht_node_custom_image.clientWidth) {
				img.style.right='';
				img.style.left='50%';
				img.style.marginLeft='-' + currentWidth/2 + 'px';
			} else {
				img.style.right='';
				img.style.left='0px';
				img.style.marginLeft='0px';
				me._ht_node_custom_image.scrollLeft=currentWidth / 2 - me._ht_node_custom_image.clientWidth / 2;
			}
			if (!me._ht_node_custom_image.ggScrollbars || currentHeight < me._ht_node_custom_image.clientHeight) {
				img.style.bottom='';
				img.style.top='0px';
			} else {
				img.style.bottom='';
				img.style.top='0px';
				img.style.marginTop='0px';
			}
		}
		me._hotspot360out.appendChild(me._ht_node_custom_image);
		el=me._rectangle_1=document.createElement('div');
		el.ggId="Rectangle 1";
		el.ggDx=0;
		el.ggDy=-26;
		el.ggParameter={ rx:0,ry:0,a:0,sx:0.35,sy:0.35,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_rectangle box-shadow: 0 1px 3px rgba(0 0 0 0.12) 0 1px 2px rgba(0 0 0 0.24);";
		el.ggType='rectangle';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 10px;';
		hs+='cursor : pointer;';
		hs+='height : 205px;';
		hs+='left : calc(50% - ((360px + 0px) / 2) + 0px);';
		hs+='overflow : hidden;';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((205px + 0px) / 2) - 26px);';
		hs+='visibility : inherit;';
		hs+='width : 360px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		el.style.transform=parameterToTransform(el.ggParameter);
		me._rectangle_1.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._rectangle_1.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['rectangle_1'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._rectangle_1.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._rectangle_1.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._rectangle_1.style.transition='transform 100ms ease 0ms';
				if (me._rectangle_1.ggCurrentLogicStateScaling == 0) {
					me._rectangle_1.ggParameter.sx = 0.39;
					me._rectangle_1.ggParameter.sy = 0.39;
					me._rectangle_1.style.transform=parameterToTransform(me._rectangle_1.ggParameter);
					setTimeout(function() {skin.updateSize(me._rectangle_1);}, 150);
				}
				else {
					me._rectangle_1.ggParameter.sx = 0.35;
					me._rectangle_1.ggParameter.sy = 0.35;
					me._rectangle_1.style.transform=parameterToTransform(me._rectangle_1.ggParameter);
					setTimeout(function() {skin.updateSize(me._rectangle_1);}, 150);
				}
			}
		}
		me._rectangle_1.logicBlock_scaling();
		me._rectangle_1.onmouseenter=function (e) {
			me.elementMouseOver['rectangle_1']=true;
			me._rectangle_1.logicBlock_scaling();
		}
		me._rectangle_1.onmouseleave=function (e) {
			me.elementMouseOver['rectangle_1']=false;
			me._rectangle_1.logicBlock_scaling();
		}
		me._rectangle_1.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_thumb_1=document.createElement('div');
		els=me._ht_node_thumb_1__img=document.createElement('img');
		els.className='ggskin ggskin_nodeimage';
		els.loading = 'lazy';
		els.setAttribute('src',basePath + "images/ht_node_thumb_1_" + player.getCurrentNode() + "_" + me.hotspot.id + ".webp");
		el.ggNodeId=nodeId;
		hs ='';
		hs += 'position: absolute;top: 0px;left: 0px;width: 100%;height: 100%;-webkit-user-drag:none;pointer-events:none;;';
		hs+='border-radius: 10px;';
		els.setAttribute('style', hs);
		els.className='ggskin ggskin_nodeimage';
		els['ondragstart']=function() { return false; };
		el.appendChild(els);
		el.ggSubElement = els;
		el.ggId="ht_node_thumb_1";
		el.ggDx=1;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_nodeimage test-css";
		el.ggType='nodeimage';
		hs ='';
		hs+='cursor : pointer;';
		hs+='height : 138px;';
		hs+='left : calc(50% - ((250px + 0px) / 2) + 1px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((138px + 0px) / 2) + 0px);';
		hs+='visibility : inherit;';
		hs+='width : 250px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_thumb_1.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			return this.ggNodeId;
		}
		me._ht_node_thumb_1.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_thumb_1'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_thumb_1.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_thumb_1.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_thumb_1.style.transition='transform 100ms ease 0ms';
				if (me._ht_node_thumb_1.ggCurrentLogicStateScaling == 0) {
					me._ht_node_thumb_1.ggParameter.sx = 1.05;
					me._ht_node_thumb_1.ggParameter.sy = 1.05;
					me._ht_node_thumb_1.style.transform=parameterToTransform(me._ht_node_thumb_1.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_thumb_1);}, 150);
				}
				else {
					me._ht_node_thumb_1.ggParameter.sx = 1;
					me._ht_node_thumb_1.ggParameter.sy = 1;
					me._ht_node_thumb_1.style.transform=parameterToTransform(me._ht_node_thumb_1.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_thumb_1);}, 150);
				}
			}
		}
		me._ht_node_thumb_1.logicBlock_scaling();
		me._ht_node_thumb_1.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_thumb_1']=true;
			me._ht_node_thumb_1.logicBlock_scaling();
		}
		me._ht_node_thumb_1.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_thumb_1']=false;
			me._ht_node_thumb_1.logicBlock_scaling();
		}
		me._ht_node_thumb_1.ggUpdatePosition=function (useTransition) {
		}
		me._rectangle_1.appendChild(me._ht_node_thumb_1);
		el=me._image_1=document.createElement('div');
		els=me._image_1__img=document.createElement('img');
		els.className='ggskin ggskin_image_1';
		hs='data:image/webp;base64,UklGRkYEAABXRUJQVlA4WAoAAAAQAAAAbAEA0wAAQUxQSBYDAAARoCv/nyJb+T9CP0I/wa3OLzIxOhkOg8OJBneYEKfrRic61Whki0vUuEPjDl1EFNH/Sv4NplfOif7oRsQEyIZdZ71ycPuYMWDNUzgAPip2XEe3Py5hzDrtx6jYcwmbcguWljh01jum2oJxM67QrKMXGw5JG6RNuMJcRzFklxqk9Vxhnp3YcldnxLUS8yjm7MqMfo2e+SAG7cpM3UpOZ0lM2hWAxUoTQBaj9grQreAUUG9VMs7yCiPAJHZdAPyyAqgzrH4Wl3iAJJZdgbpknAXTigC+lQAV0+5mfSsD2bZkNrUAJuMqQLKzDOSt/7b+2/rvf/VVIBhXBFIrLPIgxu1STq7175GdC866gvNORJy4oeZ6hrcsJ2Mq9bQg0iXPTr2z3lY6uyqOmPfylD'+
			'rHV+eKlwu+xdtVrTeKc2e8QScLqH4BRew6Qg0R1IlX7k7nosGwnBIXVyidiIT4FGkRxLJd3KUuepl37zz9cie27crek6O0yuFiXV7PSVPL7dA745KTia4l9/5+tFjX8O1xdQ0HYF0AoXVv+ewK6xrLh3dKe4LJujqYtv7b+m/rv63//mlwTv5+x7pOyl/ttAJ//PyVdXGiHPMNyYp5JeVP1wp6Wm9dXm/spOnOqVd443JX5euWdPU0OuPqCDq1/JG6643L7eqRvjGReEFH0xo08VWNIjKoDjt7x+gNK6Cxv/cYk4iio5sqxbAKGmWsqHQ8NYWrToq7eLvihXu78dzxbu3CMCye5hyVIdhVDPUB7i5dCCLuUJ3Ui237OuohJ/8u6YKlDVCsK1Q0tBQYjCsBixbAZFwZyFv/bf239d//6qtAMi4FplYGim05VokAzrT6'+
			'WdfqZ4NppZm03SxbllNgsUQWAN6wJoBhWT/LduUUULdMKkBvVhEgyorDTJ1R9QDqVpEMUJxJBZ1NsrJXgOIMKihAkTWHGcWbU68A6teROEMHW3KRZpD10wxyZ0hDpTnIJqcGlNGbUIhKUzvZbK8NQHOcbDdllpcgm/Z5iSVPsp9dNqbkZZ+7pGZUJy8HMYwpZzWdmnMcvGwSVlA4IAoBAABQFgCdASptAdQAPjEYi0QiIaEQFCggAwS0t3C7AI2gBPjRo0aNGjRo0aNGjRo0aNGjRo0aNGjRo0aNGjRo0aNGjRo0aNGjRovRtnLyZMmTJkyZLyO9MP9sBYX506dOnTp06dHffIGWTJkyZMmTJkyZMmTJkyZMmTHlhx9evXr169evXagPUAsL86dOnTp06dOjvvkDLJkyZMmTJkyZMmTJkyZMmTJkyZMmTJkyZMmTJk'+
			'yZMmTJkyZMmTHgAP7/58SAADp/qTTIW7r5kb/m7Vnpp/a7GaZuI+vX02yDleg7mNmLhx+qAACs/jAQ/GR569tmD1j6fxOgxcAnhe6omWHO8vX+BwAAAAAAAA==';
		els.setAttribute('src',hs);
		els.ggNormalSrc=hs;
		hs ='';
		hs += 'position: absolute;top: 0px;left: 0px;width: 100%;height: 100%;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.className='ggskin ggskin_image';
		els['ondragstart']=function() { return false; };
		player.checkLoaded.push(els);
		el.appendChild(els);
		el.ggSubElement = els;
		el.ggId="Image 1";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_image ";
		el.ggType='image';
		hs ='';
		hs+='height : 155px;';
		hs+='left : calc(50% - ((270px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((155px + 0px) / 2) + 0px);';
		hs+='visibility : inherit;';
		hs+='width : 270px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._image_1.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._image_1.ggUpdatePosition=function (useTransition) {
		}
		me._rectangle_1.appendChild(me._image_1);
		me._hotspot360out.appendChild(me._rectangle_1);
		el=me._txthotspot=document.createElement('div');
		els=me._txthotspot__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot";
		el.ggDx=0;
		el.ggDy=-73;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text ";
		el.ggType='text';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 5px;';
		hs+='color : rgba(255,255,255,1);';
		hs+='cursor : pointer;';
		hs+='height : 60px;';
		hs+='left : calc(50% - ((200px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((60px + 0px) / 2) - 73px);';
		hs+='visibility : inherit;';
		hs+='width : 200px;';
		hs+='pointer-events:none;';
		hs+='text-shadow: 0px 2px 3px rgba(0,0,0,0.6);';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		hs ='';
		hs += 'box-sizing: border-box;';
		hs+='width: 100%;';
		hs+='height: auto;';
		hs+='max-height: 100%;';
		hs+='pointer-events: none;';
		hs+='text-align: center;';
		hs+='position: absolute;';
		hs+='top: 50%;';
		hs+='transform: translate(0, -50%);';
		hs+='white-space: pre-line;';
		hs+='padding: 1px 1px 1px 1px;';
		hs+='overflow: hidden;';
		els.setAttribute('style',hs);
		me._txthotspot.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.title)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._txthotspot.ggUpdateText();
		player.addListener('changenode', function() {
			me._txthotspot.ggUpdateText();
		});
		el.appendChild(els);
		me._txthotspot.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._txthotspot.ggUpdatePosition=function (useTransition) {
		}
		me._hotspot360out.appendChild(me._txthotspot);
		me.elementMouseOver['hotspot360out']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image.logicBlock_visible();
		me._rectangle_1.logicBlock_scaling();
		me.elementMouseOver['rectangle_1']=false;
		me._ht_node_thumb_1.logicBlock_scaling();
		me.elementMouseOver['ht_node_thumb_1']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image.logicBlock_visible();
			};
			me.__div = me._hotspot360out;
	};
	function SkinHotspotClass__360_video_experience_project(parentScope,hotspot) {
		var me=this;
		var flag=false;
		var hs='';
		me.parentScope=parentScope;
		me.hotspot=hotspot;
		var nodeId=String(hotspot.url);
		nodeId=(nodeId.charAt(0)=='{')?nodeId.substr(1, nodeId.length - 2):''; // }
		me.ggUserdata=skin.player.getNodeUserdata(nodeId);
		me.elementMouseDown={};
		me.elementMouseOver={};
		me.findElements=function(id,regex) {
			return skin.findElements(id,regex);
		}
		el=me.__360_video_experience_project=document.createElement('div');
		el.ggId="360_video_experience_project";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_hotspot ";
		el.ggType='hotspot';
		hs ='';
		hs+='z-index: -1;';
		hs+='cursor : pointer;';
		hs+='height : 0px;';
		hs+='left : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='visibility : inherit;';
		hs+='width : 0px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me.__360_video_experience_project.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			if (me.hotspot.url!='' && me.hotspot.url.charAt(0)=='{') { // }
				return me.hotspot.url.substr(1, me.hotspot.url.length - 2);
			} else {
				if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
					return this.parentNode.ggElementNodeId();
				} else {
					return player.getCurrentNode();
				}
			}
		}
		me.__360_video_experience_project.onclick=function (e) {
			player.openUrl(player._(me.hotspot.url),"_self");
			player.triggerEvent('hsproxyclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me.__360_video_experience_project.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me.__360_video_experience_project.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['_360_video_experience_project']=true;
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me.__360_video_experience_project.onmouseleave=function (e) {
			me.elementMouseOver['_360_video_experience_project']=false;
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me.__360_video_experience_project.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_custom_image0=document.createElement('div');
		els=me._ht_node_custom_image0__img=document.createElement('img');
		els.className='ggskin ggskin_external';
		hs ='';
		hs += 'position: absolute;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.onload=function() {me._ht_node_custom_image0.ggUpdatePosition();}
		el.appendChild(els);
		el.ggSubElement = els;
		hs ='';
		el.ggAltText="";
		el.ggScrollbars=false;
		el.ggUpdateText = function() {
			me._ht_node_custom_image0.ggSubElement.setAttribute('alt', player._(me._ht_node_custom_image0.ggAltText));
			me._ht_node_custom_image0.ggUpdateImageTranslation();
		}
		el.ggSetImage = function(img) {
			me._ht_node_custom_image0.ggText_untranslated = img;
			me._ht_node_custom_image0.ggUpdateImageTranslation();
		}
		el.ggUpdateImage = function() {
			me._ht_node_custom_image0.ggSubElement.style.width = '0px';
			me._ht_node_custom_image0.ggSubElement.style.height = '0px';
			me._ht_node_custom_image0.ggSubElement.src='';
			me._ht_node_custom_image0.ggSubElement.src=me._ht_node_custom_image0.ggText;
		}
		el.ggUpdateImageTranslation = function() {
			if (me._ht_node_custom_image0.ggText != player._(me._ht_node_custom_image0.ggText_untranslated)) {
				me._ht_node_custom_image0.ggText = player._(me._ht_node_custom_image0.ggText_untranslated);
				me._ht_node_custom_image0.ggUpdateImage()
			}
		}
		if ((hotspot) && (hotspot.customimage)) {
			el.ggText=el.ggText_untranslated=hotspot.customimage;
			els.setAttribute('src', hotspot.customimage);
			els.style.width=hotspot.customimagewidth + 'px';
			els.style.height=hotspot.customimageheight + 'px';
			me.ggUse3d = hotspot.use3D;
			me.gg3dDistance = hotspot.distance3D;
		}
		els['ondragstart']=function() { return false; };
		player.checkLoaded.push(els);
		el.ggUpdateText();
		el.ggId="ht_node_custom_image";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_external ";
		el.ggType='external';
		hs ='';
		hs+='border : 0px solid #000000;';
		hs+='cursor : pointer;';
		hs+='height : 100%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='visibility : hidden;';
		hs+='width : 100%;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_custom_image0.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._ht_node_custom_image0.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_custom_image0'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_custom_image0.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_custom_image0.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_custom_image0.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image0.ggCurrentLogicStateScaling == 0) {
					me._ht_node_custom_image0.ggParameter.sx = 1.04;
					me._ht_node_custom_image0.ggParameter.sy = 1.04;
					me._ht_node_custom_image0.style.transform=parameterToTransform(me._ht_node_custom_image0.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image0);}, 250);
				}
				else {
					me._ht_node_custom_image0.ggParameter.sx = 1;
					me._ht_node_custom_image0.ggParameter.sy = 1;
					me._ht_node_custom_image0.style.transform=parameterToTransform(me._ht_node_custom_image0.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image0);}, 250);
				}
			}
		}
		me._ht_node_custom_image0.logicBlock_scaling();
		me._ht_node_custom_image0.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((me.hotspot.customimage != ""))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._ht_node_custom_image0.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._ht_node_custom_image0.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._ht_node_custom_image0.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image0.ggCurrentLogicStateVisible == 0) {
					me._ht_node_custom_image0.style.visibility=(Number(me._ht_node_custom_image0.style.opacity)>0||!me._ht_node_custom_image0.style.opacity)?'inherit':'hidden';
					me._ht_node_custom_image0.ggSubElement.src=me._ht_node_custom_image0.ggText;
					me._ht_node_custom_image0.ggVisible=true;
				}
				else {
					me._ht_node_custom_image0.style.visibility="hidden";
					me._ht_node_custom_image0.ggSubElement.src='';
					me._ht_node_custom_image0.ggVisible=false;
				}
			}
		}
		me._ht_node_custom_image0.logicBlock_visible();
		me._ht_node_custom_image0.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_custom_image0']=true;
			me._ht_node_custom_image0.logicBlock_scaling();
		}
		me._ht_node_custom_image0.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_custom_image0']=false;
			me._ht_node_custom_image0.logicBlock_scaling();
		}
		me._ht_node_custom_image0.ggUpdatePosition=function (useTransition) {
			var parentWidth = me._ht_node_custom_image0.clientWidth;
			var parentHeight = me._ht_node_custom_image0.clientHeight;
			var img = me._ht_node_custom_image0__img;
			var aspectRatioDiv = me._ht_node_custom_image0.clientWidth / me._ht_node_custom_image0.clientHeight;
			var aspectRatioImg = img.naturalWidth / img.naturalHeight;
			var currentWidth,currentHeight;
			if ((hotspot) && (hotspot.customimage)) {
				currentWidth  = hotspot.customimagewidth;
				currentHeight = hotspot.customimageheight;
			}
			if (!me._ht_node_custom_image0.ggScrollbars || currentWidth < me._ht_node_custom_image0.clientWidth) {
				img.style.right='';
				img.style.left='50%';
				img.style.marginLeft='-' + currentWidth/2 + 'px';
			} else {
				img.style.right='';
				img.style.left='0px';
				img.style.marginLeft='0px';
				me._ht_node_custom_image0.scrollLeft=currentWidth / 2 - me._ht_node_custom_image0.clientWidth / 2;
			}
			if (!me._ht_node_custom_image0.ggScrollbars || currentHeight < me._ht_node_custom_image0.clientHeight) {
				img.style.bottom='';
				img.style.top='0px';
			} else {
				img.style.bottom='';
				img.style.top='0px';
				img.style.marginTop='0px';
			}
		}
		me.__360_video_experience_project.appendChild(me._ht_node_custom_image0);
		el=me._txthotspot360=document.createElement('div');
		els=me._txthotspot360__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot-360";
		el.ggDx=0;
		el.ggDy=-150;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text ";
		el.ggType='text';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 5px;';
		hs+='color : rgba(255,255,255,1);';
		hs+='height : 60px;';
		hs+='left : calc(50% - ((200px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((60px + 0px) / 2) - 150px);';
		hs+='visibility : inherit;';
		hs+='width : 200px;';
		hs+='pointer-events:none;';
		hs+='text-shadow: 0px 2px 3px rgba(0,0,0,0.6);';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		hs ='';
		hs += 'box-sizing: border-box;';
		hs+='width: 100%;';
		hs+='height: auto;';
		hs+='max-height: 100%;';
		hs+='pointer-events: none;';
		hs+='text-align: center;';
		hs+='position: absolute;';
		hs+='top: 50%;';
		hs+='transform: translate(0, -50%);';
		hs+='white-space: pre-line;';
		hs+='padding: 1px 1px 1px 1px;';
		hs+='overflow: hidden;';
		els.setAttribute('style',hs);
		me._txthotspot360.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.title)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._txthotspot360.ggUpdateText();
		player.addListener('changenode', function() {
			me._txthotspot360.ggUpdateText();
		});
		el.appendChild(els);
		me._txthotspot360.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._txthotspot360.ggUpdatePosition=function (useTransition) {
		}
		me.__360_video_experience_project.appendChild(me._txthotspot360);
		me.elementMouseOver['_360_video_experience_project']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image0.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image0.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image0.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image0.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image0.logicBlock_scaling();
		me._ht_node_custom_image0.logicBlock_visible();
		me.elementMouseOver['ht_node_custom_image0']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image0.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image0.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image0.logicBlock_visible();
			};
			me.__div = me.__360_video_experience_project;
	};
	function SkinHotspotClass_box_ui_location_kategori(parentScope,hotspot) {
		var me=this;
		var flag=false;
		var hs='';
		me.parentScope=parentScope;
		me.hotspot=hotspot;
		var nodeId=String(hotspot.url);
		nodeId=(nodeId.charAt(0)=='{')?nodeId.substr(1, nodeId.length - 2):''; // }
		me.ggUserdata=skin.player.getNodeUserdata(nodeId);
		me.elementMouseDown={};
		me.elementMouseOver={};
		me.findElements=function(id,regex) {
			return skin.findElements(id,regex);
		}
		el=me._box_ui_location_kategori=document.createElement('div');
		el.ggId="box_ui_location_kategori";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_hotspot ";
		el.ggType='hotspot';
		hs ='';
		hs+='z-index: -1;';
		hs+='cursor : pointer;';
		hs+='height : 0px;';
		hs+='left : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='visibility : inherit;';
		hs+='width : 0px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._box_ui_location_kategori.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			if (me.hotspot.url!='' && me.hotspot.url.charAt(0)=='{') { // }
				return me.hotspot.url.substr(1, me.hotspot.url.length - 2);
			} else {
				if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
					return this.parentNode.ggElementNodeId();
				} else {
					return player.getCurrentNode();
				}
			}
		}
		me._box_ui_location_kategori.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((player.getVariableValue('vis_skin') == true))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._box_ui_location_kategori.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._box_ui_location_kategori.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._box_ui_location_kategori.style.transition='';
				if (me._box_ui_location_kategori.ggCurrentLogicStateVisible == 0) {
					me._box_ui_location_kategori.style.visibility=(Number(me._box_ui_location_kategori.style.opacity)>0||!me._box_ui_location_kategori.style.opacity)?'inherit':'hidden';
					me._box_ui_location_kategori.ggVisible=true;
				}
				else {
					me._box_ui_location_kategori.style.visibility=(Number(me._box_ui_location_kategori.style.opacity)>0||!me._box_ui_location_kategori.style.opacity)?'inherit':'hidden';
					me._box_ui_location_kategori.ggVisible=true;
				}
			}
		}
		me._box_ui_location_kategori.logicBlock_visible();
		me._box_ui_location_kategori.onclick=function (e) {
			player.openUrl(player._(me.hotspot.url),"_self");
			player.triggerEvent('hsproxyclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._box_ui_location_kategori.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._box_ui_location_kategori.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['box_ui_location_kategori']=true;
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._box_ui_location_kategori.onmouseleave=function (e) {
			me.elementMouseOver['box_ui_location_kategori']=false;
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me._box_ui_location_kategori.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_custom_image1=document.createElement('div');
		els=me._ht_node_custom_image1__img=document.createElement('img');
		els.className='ggskin ggskin_external';
		hs ='';
		hs += 'position: absolute;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.onload=function() {me._ht_node_custom_image1.ggUpdatePosition();}
		el.appendChild(els);
		el.ggSubElement = els;
		hs ='';
		el.ggAltText="";
		el.ggScrollbars=false;
		el.ggUpdateText = function() {
			me._ht_node_custom_image1.ggSubElement.setAttribute('alt', player._(me._ht_node_custom_image1.ggAltText));
			me._ht_node_custom_image1.ggUpdateImageTranslation();
		}
		el.ggSetImage = function(img) {
			me._ht_node_custom_image1.ggText_untranslated = img;
			me._ht_node_custom_image1.ggUpdateImageTranslation();
		}
		el.ggUpdateImage = function() {
			me._ht_node_custom_image1.ggSubElement.style.width = '0px';
			me._ht_node_custom_image1.ggSubElement.style.height = '0px';
			me._ht_node_custom_image1.ggSubElement.src='';
			me._ht_node_custom_image1.ggSubElement.src=me._ht_node_custom_image1.ggText;
		}
		el.ggUpdateImageTranslation = function() {
			if (me._ht_node_custom_image1.ggText != player._(me._ht_node_custom_image1.ggText_untranslated)) {
				me._ht_node_custom_image1.ggText = player._(me._ht_node_custom_image1.ggText_untranslated);
				me._ht_node_custom_image1.ggUpdateImage()
			}
		}
		if ((hotspot) && (hotspot.customimage)) {
			el.ggText=el.ggText_untranslated=hotspot.customimage;
			els.setAttribute('src', hotspot.customimage);
			els.style.width=hotspot.customimagewidth + 'px';
			els.style.height=hotspot.customimageheight + 'px';
			me.ggUse3d = hotspot.use3D;
			me.gg3dDistance = hotspot.distance3D;
		}
		els['ondragstart']=function() { return false; };
		player.checkLoaded.push(els);
		el.ggUpdateText();
		el.ggId="ht_node_custom_image";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_external ";
		el.ggType='external';
		hs ='';
		hs+='border : 0px solid #000000;';
		hs+='cursor : pointer;';
		hs+='height : 100%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='visibility : hidden;';
		hs+='width : 100%;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_custom_image1.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._ht_node_custom_image1.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_custom_image1'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_custom_image1.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_custom_image1.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_custom_image1.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image1.ggCurrentLogicStateScaling == 0) {
					me._ht_node_custom_image1.ggParameter.sx = 1.04;
					me._ht_node_custom_image1.ggParameter.sy = 1.04;
					me._ht_node_custom_image1.style.transform=parameterToTransform(me._ht_node_custom_image1.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image1);}, 250);
				}
				else {
					me._ht_node_custom_image1.ggParameter.sx = 1;
					me._ht_node_custom_image1.ggParameter.sy = 1;
					me._ht_node_custom_image1.style.transform=parameterToTransform(me._ht_node_custom_image1.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image1);}, 250);
				}
			}
		}
		me._ht_node_custom_image1.logicBlock_scaling();
		me._ht_node_custom_image1.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((me.hotspot.customimage != ""))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._ht_node_custom_image1.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._ht_node_custom_image1.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._ht_node_custom_image1.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image1.ggCurrentLogicStateVisible == 0) {
					me._ht_node_custom_image1.style.visibility=(Number(me._ht_node_custom_image1.style.opacity)>0||!me._ht_node_custom_image1.style.opacity)?'inherit':'hidden';
					me._ht_node_custom_image1.ggSubElement.src=me._ht_node_custom_image1.ggText;
					me._ht_node_custom_image1.ggVisible=true;
				}
				else {
					me._ht_node_custom_image1.style.visibility="hidden";
					me._ht_node_custom_image1.ggSubElement.src='';
					me._ht_node_custom_image1.ggVisible=false;
				}
			}
		}
		me._ht_node_custom_image1.logicBlock_visible();
		me._ht_node_custom_image1.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_custom_image1']=true;
			me._ht_node_custom_image1.logicBlock_scaling();
		}
		me._ht_node_custom_image1.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_custom_image1']=false;
			me._ht_node_custom_image1.logicBlock_scaling();
		}
		me._ht_node_custom_image1.ggUpdatePosition=function (useTransition) {
			var parentWidth = me._ht_node_custom_image1.clientWidth;
			var parentHeight = me._ht_node_custom_image1.clientHeight;
			var img = me._ht_node_custom_image1__img;
			var aspectRatioDiv = me._ht_node_custom_image1.clientWidth / me._ht_node_custom_image1.clientHeight;
			var aspectRatioImg = img.naturalWidth / img.naturalHeight;
			var currentWidth,currentHeight;
			if ((hotspot) && (hotspot.customimage)) {
				currentWidth  = hotspot.customimagewidth;
				currentHeight = hotspot.customimageheight;
			}
			if (!me._ht_node_custom_image1.ggScrollbars || currentWidth < me._ht_node_custom_image1.clientWidth) {
				img.style.right='';
				img.style.left='50%';
				img.style.marginLeft='-' + currentWidth/2 + 'px';
			} else {
				img.style.right='';
				img.style.left='0px';
				img.style.marginLeft='0px';
				me._ht_node_custom_image1.scrollLeft=currentWidth / 2 - me._ht_node_custom_image1.clientWidth / 2;
			}
			if (!me._ht_node_custom_image1.ggScrollbars || currentHeight < me._ht_node_custom_image1.clientHeight) {
				img.style.bottom='';
				img.style.top='0px';
			} else {
				img.style.bottom='';
				img.style.top='0px';
				img.style.marginTop='0px';
			}
		}
		me._box_ui_location_kategori.appendChild(me._ht_node_custom_image1);
		el=me._txthotspot0=document.createElement('div');
		els=me._txthotspot0__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot";
		el.ggDx=0;
		el.ggDy=-150;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text ";
		el.ggType='text';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 5px;';
		hs+='color : rgba(255,255,255,1);';
		hs+='height : 60px;';
		hs+='left : calc(50% - ((200px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((60px + 0px) / 2) - 150px);';
		hs+='visibility : inherit;';
		hs+='width : 200px;';
		hs+='pointer-events:none;';
		hs+='text-shadow: 0px 2px 3px rgba(0,0,0,0.6);';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		hs ='';
		hs += 'box-sizing: border-box;';
		hs+='width: 100%;';
		hs+='height: auto;';
		hs+='max-height: 100%;';
		hs+='pointer-events: none;';
		hs+='text-align: center;';
		hs+='position: absolute;';
		hs+='top: 50%;';
		hs+='transform: translate(0, -50%);';
		hs+='white-space: pre-line;';
		hs+='padding: 1px 1px 1px 1px;';
		hs+='overflow: hidden;';
		els.setAttribute('style',hs);
		me._txthotspot0.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.title)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._txthotspot0.ggUpdateText();
		player.addListener('changenode', function() {
			me._txthotspot0.ggUpdateText();
		});
		el.appendChild(els);
		me._txthotspot0.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._txthotspot0.ggUpdatePosition=function (useTransition) {
		}
		me._box_ui_location_kategori.appendChild(me._txthotspot0);
		me._box_ui_location_kategori.logicBlock_visible();
		me.elementMouseOver['box_ui_location_kategori']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image1.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image1.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image1.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image1.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image1.logicBlock_scaling();
		me._ht_node_custom_image1.logicBlock_visible();
		me.elementMouseOver['ht_node_custom_image1']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image1.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._box_ui_location_kategori.logicBlock_visible();
				me._ht_node_custom_image1.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._box_ui_location_kategori.logicBlock_visible();
				me._ht_node_custom_image1.logicBlock_visible();
			};
			me.ggEvent_varchanged_vis_skin=function() {
				me._box_ui_location_kategori.logicBlock_visible();
			};
			me.__div = me._box_ui_location_kategori;
	};
	function SkinHotspotClass_drone_icon_project(parentScope,hotspot) {
		var me=this;
		var flag=false;
		var hs='';
		me.parentScope=parentScope;
		me.hotspot=hotspot;
		var nodeId=String(hotspot.url);
		nodeId=(nodeId.charAt(0)=='{')?nodeId.substr(1, nodeId.length - 2):''; // }
		me.ggUserdata=skin.player.getNodeUserdata(nodeId);
		me.elementMouseDown={};
		me.elementMouseOver={};
		me.findElements=function(id,regex) {
			return skin.findElements(id,regex);
		}
		el=me._drone_icon_project=document.createElement('div');
		el.ggId="drone_icon_project";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_hotspot ";
		el.ggType='hotspot';
		hs ='';
		hs+='z-index: -1;';
		hs+='cursor : pointer;';
		hs+='height : 0px;';
		hs+='left : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='visibility : inherit;';
		hs+='width : 0px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._drone_icon_project.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			if (me.hotspot.url!='' && me.hotspot.url.charAt(0)=='{') { // }
				return me.hotspot.url.substr(1, me.hotspot.url.length - 2);
			} else {
				if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
					return this.parentNode.ggElementNodeId();
				} else {
					return player.getCurrentNode();
				}
			}
		}
		me._drone_icon_project.onclick=function (e) {
			player.openNext(player._(me.hotspot.url),player._(me.hotspot.target));
			var list=me.findElements("hotspot-360-out",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			player.triggerEvent('hsproxyclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._drone_icon_project.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._drone_icon_project.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['drone_icon_project']=true;
			me._rectangle_1_1.logicBlock_alpha();
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._drone_icon_project.onmouseleave=function (e) {
			me.elementMouseOver['drone_icon_project']=false;
			me._rectangle_1_1.logicBlock_alpha();
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me._drone_icon_project.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_custom_image2=document.createElement('div');
		els=me._ht_node_custom_image2__img=document.createElement('img');
		els.className='ggskin ggskin_external';
		hs ='';
		hs += 'position: absolute;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.onload=function() {me._ht_node_custom_image2.ggUpdatePosition();}
		el.appendChild(els);
		el.ggSubElement = els;
		hs ='';
		el.ggAltText="";
		el.ggScrollbars=false;
		el.ggUpdateText = function() {
			me._ht_node_custom_image2.ggSubElement.setAttribute('alt', player._(me._ht_node_custom_image2.ggAltText));
			me._ht_node_custom_image2.ggUpdateImageTranslation();
		}
		el.ggSetImage = function(img) {
			me._ht_node_custom_image2.ggText_untranslated = img;
			me._ht_node_custom_image2.ggUpdateImageTranslation();
		}
		el.ggUpdateImage = function() {
			me._ht_node_custom_image2.ggSubElement.style.width = '0px';
			me._ht_node_custom_image2.ggSubElement.style.height = '0px';
			me._ht_node_custom_image2.ggSubElement.src='';
			me._ht_node_custom_image2.ggSubElement.src=me._ht_node_custom_image2.ggText;
		}
		el.ggUpdateImageTranslation = function() {
			if (me._ht_node_custom_image2.ggText != player._(me._ht_node_custom_image2.ggText_untranslated)) {
				me._ht_node_custom_image2.ggText = player._(me._ht_node_custom_image2.ggText_untranslated);
				me._ht_node_custom_image2.ggUpdateImage()
			}
		}
		if ((hotspot) && (hotspot.customimage)) {
			el.ggText=el.ggText_untranslated=hotspot.customimage;
			els.setAttribute('src', hotspot.customimage);
			els.style.width=hotspot.customimagewidth + 'px';
			els.style.height=hotspot.customimageheight + 'px';
			me.ggUse3d = hotspot.use3D;
			me.gg3dDistance = hotspot.distance3D;
		}
		els['ondragstart']=function() { return false; };
		player.checkLoaded.push(els);
		el.ggUpdateText();
		el.ggId="ht_node_custom_image";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_external ";
		el.ggType='external';
		hs ='';
		hs+='border : 0px solid #000000;';
		hs+='cursor : pointer;';
		hs+='height : 100%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='visibility : hidden;';
		hs+='width : 100%;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_custom_image2.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._ht_node_custom_image2.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_custom_image2'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_custom_image2.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_custom_image2.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_custom_image2.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image2.ggCurrentLogicStateScaling == 0) {
					me._ht_node_custom_image2.ggParameter.sx = 1.04;
					me._ht_node_custom_image2.ggParameter.sy = 1.04;
					me._ht_node_custom_image2.style.transform=parameterToTransform(me._ht_node_custom_image2.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image2);}, 250);
				}
				else {
					me._ht_node_custom_image2.ggParameter.sx = 1;
					me._ht_node_custom_image2.ggParameter.sy = 1;
					me._ht_node_custom_image2.style.transform=parameterToTransform(me._ht_node_custom_image2.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image2);}, 250);
				}
			}
		}
		me._ht_node_custom_image2.logicBlock_scaling();
		me._ht_node_custom_image2.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((me.hotspot.customimage != ""))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._ht_node_custom_image2.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._ht_node_custom_image2.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._ht_node_custom_image2.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image2.ggCurrentLogicStateVisible == 0) {
					me._ht_node_custom_image2.style.visibility=(Number(me._ht_node_custom_image2.style.opacity)>0||!me._ht_node_custom_image2.style.opacity)?'inherit':'hidden';
					me._ht_node_custom_image2.ggSubElement.src=me._ht_node_custom_image2.ggText;
					me._ht_node_custom_image2.ggVisible=true;
				}
				else {
					me._ht_node_custom_image2.style.visibility="hidden";
					me._ht_node_custom_image2.ggSubElement.src='';
					me._ht_node_custom_image2.ggVisible=false;
				}
			}
		}
		me._ht_node_custom_image2.logicBlock_visible();
		me._ht_node_custom_image2.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_custom_image2']=true;
			me._ht_node_custom_image2.logicBlock_scaling();
		}
		me._ht_node_custom_image2.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_custom_image2']=false;
			me._ht_node_custom_image2.logicBlock_scaling();
		}
		me._ht_node_custom_image2.ggUpdatePosition=function (useTransition) {
			var parentWidth = me._ht_node_custom_image2.clientWidth;
			var parentHeight = me._ht_node_custom_image2.clientHeight;
			var img = me._ht_node_custom_image2__img;
			var aspectRatioDiv = me._ht_node_custom_image2.clientWidth / me._ht_node_custom_image2.clientHeight;
			var aspectRatioImg = img.naturalWidth / img.naturalHeight;
			var currentWidth,currentHeight;
			if ((hotspot) && (hotspot.customimage)) {
				currentWidth  = hotspot.customimagewidth;
				currentHeight = hotspot.customimageheight;
			}
			if (!me._ht_node_custom_image2.ggScrollbars || currentWidth < me._ht_node_custom_image2.clientWidth) {
				img.style.right='';
				img.style.left='50%';
				img.style.marginLeft='-' + currentWidth/2 + 'px';
			} else {
				img.style.right='';
				img.style.left='0px';
				img.style.marginLeft='0px';
				me._ht_node_custom_image2.scrollLeft=currentWidth / 2 - me._ht_node_custom_image2.clientWidth / 2;
			}
			if (!me._ht_node_custom_image2.ggScrollbars || currentHeight < me._ht_node_custom_image2.clientHeight) {
				img.style.bottom='';
				img.style.top='0px';
			} else {
				img.style.bottom='';
				img.style.top='0px';
				img.style.marginTop='0px';
			}
		}
		me._drone_icon_project.appendChild(me._ht_node_custom_image2);
		el=me._rectangle_1_1=document.createElement('div');
		el.ggId="Rectangle 1_1";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:0.35,sy:0.35,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_rectangle box-shadow: 0 1px 3px rgba(0 0 0 0.12) 0 1px 2px rgba(0 0 0 0.24);";
		el.ggType='rectangle';
		hs ='';
		hs+='background : #ffffff;';
		hs+='border : 1px solid #949494;';
		hs+='border-radius : 10px;';
		hs+='height : 159px;';
		hs+='left : calc(50% - ((278px + 2px) / 2) + 0px);';
		hs+='opacity : 0;';
		hs+='overflow : hidden;';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((159px + 2px) / 2) + 0px);';
		hs+='visibility : hidden;';
		hs+='width : 278px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		el.style.transform=parameterToTransform(el.ggParameter);
		me._rectangle_1_1.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._rectangle_1_1.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['rectangle_1_1'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._rectangle_1_1.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._rectangle_1_1.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._rectangle_1_1.style.transition='transform 100ms ease 0ms, opacity 500ms ease 0ms';
				if (me._rectangle_1_1.ggCurrentLogicStateScaling == 0) {
					me._rectangle_1_1.ggParameter.sx = 0.39;
					me._rectangle_1_1.ggParameter.sy = 0.39;
					me._rectangle_1_1.style.transform=parameterToTransform(me._rectangle_1_1.ggParameter);
					setTimeout(function() {skin.updateSize(me._rectangle_1_1);}, 150);
				}
				else {
					me._rectangle_1_1.ggParameter.sx = 0.35;
					me._rectangle_1_1.ggParameter.sy = 0.35;
					me._rectangle_1_1.style.transform=parameterToTransform(me._rectangle_1_1.ggParameter);
					setTimeout(function() {skin.updateSize(me._rectangle_1_1);}, 150);
				}
			}
		}
		me._rectangle_1_1.logicBlock_scaling();
		me._rectangle_1_1.logicBlock_alpha = function() {
			var newLogicStateAlpha;
			if (
				((me.elementMouseOver['drone_icon_project'] == true))
			)
			{
				newLogicStateAlpha = 0;
			}
			else {
				newLogicStateAlpha = -1;
			}
			if (me._rectangle_1_1.ggCurrentLogicStateAlpha != newLogicStateAlpha) {
				me._rectangle_1_1.ggCurrentLogicStateAlpha = newLogicStateAlpha;
				me._rectangle_1_1.style.transition='transform 100ms ease 0ms, opacity 500ms ease 0ms';
				if (me._rectangle_1_1.ggCurrentLogicStateAlpha == 0) {
					me._rectangle_1_1.style.visibility=me._rectangle_1_1.ggVisible?'inherit':'hidden';
					me._rectangle_1_1.style.opacity=1;
				}
				else {
					setTimeout(function() { if (me._rectangle_1_1.style.opacity == 0.0) { me._rectangle_1_1.style.visibility="hidden"; } }, 505);
					me._rectangle_1_1.style.opacity=0;
				}
			}
		}
		me._rectangle_1_1.logicBlock_alpha();
		me._rectangle_1_1.onmouseenter=function (e) {
			me.elementMouseOver['rectangle_1_1']=true;
			me._rectangle_1_1.logicBlock_scaling();
		}
		me._rectangle_1_1.onmouseleave=function (e) {
			me.elementMouseOver['rectangle_1_1']=false;
			me._rectangle_1_1.logicBlock_scaling();
		}
		me._rectangle_1_1.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_thumb_10=document.createElement('div');
		els=me._ht_node_thumb_10__img=document.createElement('img');
		els.className='ggskin ggskin_nodeimage';
		els.loading = 'lazy';
		els.setAttribute('src',basePath + "images/ht_node_thumb_1_" + player.getCurrentNode() + "_" + me.hotspot.id + ".webp");
		el.ggNodeId=nodeId;
		hs ='';
		hs += 'position: absolute;top: 0px;left: 0px;width: 100%;height: 100%;-webkit-user-drag:none;pointer-events:none;;';
		hs+='border-radius: 10px;';
		els.setAttribute('style', hs);
		els.className='ggskin ggskin_nodeimage';
		els['ondragstart']=function() { return false; };
		el.appendChild(els);
		el.ggSubElement = els;
		el.ggId="ht_node_thumb_1";
		el.ggDx=1;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_nodeimage test-css";
		el.ggType='nodeimage';
		hs ='';
		hs+='cursor : pointer;';
		hs+='height : 138px;';
		hs+='left : calc(50% - ((250px + 0px) / 2) + 1px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((138px + 0px) / 2) + 0px);';
		hs+='visibility : inherit;';
		hs+='width : 250px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_thumb_10.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			return this.ggNodeId;
		}
		me._ht_node_thumb_10.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_thumb_10'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_thumb_10.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_thumb_10.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_thumb_10.style.transition='transform 100ms ease 0ms';
				if (me._ht_node_thumb_10.ggCurrentLogicStateScaling == 0) {
					me._ht_node_thumb_10.ggParameter.sx = 1.05;
					me._ht_node_thumb_10.ggParameter.sy = 1.05;
					me._ht_node_thumb_10.style.transform=parameterToTransform(me._ht_node_thumb_10.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_thumb_10);}, 150);
				}
				else {
					me._ht_node_thumb_10.ggParameter.sx = 1;
					me._ht_node_thumb_10.ggParameter.sy = 1;
					me._ht_node_thumb_10.style.transform=parameterToTransform(me._ht_node_thumb_10.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_thumb_10);}, 150);
				}
			}
		}
		me._ht_node_thumb_10.logicBlock_scaling();
		me._ht_node_thumb_10.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_thumb_10']=true;
			me._ht_node_thumb_10.logicBlock_scaling();
		}
		me._ht_node_thumb_10.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_thumb_10']=false;
			me._ht_node_thumb_10.logicBlock_scaling();
		}
		me._ht_node_thumb_10.ggUpdatePosition=function (useTransition) {
		}
		me._rectangle_1_1.appendChild(me._ht_node_thumb_10);
		me._drone_icon_project.appendChild(me._rectangle_1_1);
		el=me._txthotspot1=document.createElement('div');
		els=me._txthotspot1__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot";
		el.ggDx=0;
		el.ggDy=-42;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text ";
		el.ggType='text';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 5px;';
		hs+='color : rgba(255,255,255,1);';
		hs+='height : 60px;';
		hs+='left : calc(50% - ((200px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((60px + 0px) / 2) - 42px);';
		hs+='visibility : inherit;';
		hs+='width : 200px;';
		hs+='pointer-events:none;';
		hs+='text-shadow: 0px 2px 3px rgba(0,0,0,0.6);';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		hs ='';
		hs += 'box-sizing: border-box;';
		hs+='width: 100%;';
		hs+='height: auto;';
		hs+='max-height: 100%;';
		hs+='pointer-events: none;';
		hs+='text-align: center;';
		hs+='position: absolute;';
		hs+='top: 50%;';
		hs+='transform: translate(0, -50%);';
		hs+='white-space: pre-line;';
		hs+='padding: 1px 1px 1px 1px;';
		hs+='overflow: hidden;';
		els.setAttribute('style',hs);
		me._txthotspot1.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.title)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._txthotspot1.ggUpdateText();
		player.addListener('changenode', function() {
			me._txthotspot1.ggUpdateText();
		});
		el.appendChild(els);
		me._txthotspot1.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._txthotspot1.ggUpdatePosition=function (useTransition) {
		}
		me._drone_icon_project.appendChild(me._txthotspot1);
		me.elementMouseOver['drone_icon_project']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image2.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image2.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image2.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image2.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image2.logicBlock_scaling();
		me._ht_node_custom_image2.logicBlock_visible();
		me.elementMouseOver['ht_node_custom_image2']=false;
		me._rectangle_1_1.logicBlock_scaling();
		me._rectangle_1_1.logicBlock_alpha();
		me.elementMouseOver['rectangle_1_1']=false;
		me._ht_node_thumb_10.logicBlock_scaling();
		me.elementMouseOver['ht_node_thumb_10']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image2.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image2.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image2.logicBlock_visible();
			};
			me.__div = me._drone_icon_project;
	};
	function SkinHotspotClass_box_ui_location_project(parentScope,hotspot) {
		var me=this;
		var flag=false;
		var hs='';
		me.parentScope=parentScope;
		me.hotspot=hotspot;
		var nodeId=String(hotspot.url);
		nodeId=(nodeId.charAt(0)=='{')?nodeId.substr(1, nodeId.length - 2):''; // }
		me.ggUserdata=skin.player.getNodeUserdata(nodeId);
		me.elementMouseDown={};
		me.elementMouseOver={};
		me.findElements=function(id,regex) {
			return skin.findElements(id,regex);
		}
		el=me._box_ui_location_project=document.createElement('div');
		el.ggId="box_ui_location_project";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_hotspot ";
		el.ggType='hotspot';
		hs ='';
		hs+='z-index: -1;';
		hs+='cursor : pointer;';
		hs+='height : 0px;';
		hs+='left : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='visibility : inherit;';
		hs+='width : 0px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._box_ui_location_project.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			if (me.hotspot.url!='' && me.hotspot.url.charAt(0)=='{') { // }
				return me.hotspot.url.substr(1, me.hotspot.url.length - 2);
			} else {
				if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
					return this.parentNode.ggElementNodeId();
				} else {
					return player.getCurrentNode();
				}
			}
		}
		me._box_ui_location_project.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((player.getVariableValue('vis_skin') == true))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._box_ui_location_project.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._box_ui_location_project.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._box_ui_location_project.style.transition='';
				if (me._box_ui_location_project.ggCurrentLogicStateVisible == 0) {
					me._box_ui_location_project.style.visibility=(Number(me._box_ui_location_project.style.opacity)>0||!me._box_ui_location_project.style.opacity)?'inherit':'hidden';
					me._box_ui_location_project.ggVisible=true;
				}
				else {
					me._box_ui_location_project.style.visibility=(Number(me._box_ui_location_project.style.opacity)>0||!me._box_ui_location_project.style.opacity)?'inherit':'hidden';
					me._box_ui_location_project.ggVisible=true;
				}
			}
		}
		me._box_ui_location_project.logicBlock_visible();
		me._box_ui_location_project.onclick=function (e) {
			player.openNext(player._(me.hotspot.url),player._(me.hotspot.target));
			var list=me.findElements("hotspot-360-out",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			player.triggerEvent('hsproxyclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._box_ui_location_project.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._box_ui_location_project.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['box_ui_location_project']=true;
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._box_ui_location_project.onmouseleave=function (e) {
			me.elementMouseOver['box_ui_location_project']=false;
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me._box_ui_location_project.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_custom_image3=document.createElement('div');
		els=me._ht_node_custom_image3__img=document.createElement('img');
		els.className='ggskin ggskin_external';
		hs ='';
		hs += 'position: absolute;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.onload=function() {me._ht_node_custom_image3.ggUpdatePosition();}
		el.appendChild(els);
		el.ggSubElement = els;
		hs ='';
		el.ggAltText="";
		el.ggScrollbars=false;
		el.ggUpdateText = function() {
			me._ht_node_custom_image3.ggSubElement.setAttribute('alt', player._(me._ht_node_custom_image3.ggAltText));
			me._ht_node_custom_image3.ggUpdateImageTranslation();
		}
		el.ggSetImage = function(img) {
			me._ht_node_custom_image3.ggText_untranslated = img;
			me._ht_node_custom_image3.ggUpdateImageTranslation();
		}
		el.ggUpdateImage = function() {
			me._ht_node_custom_image3.ggSubElement.style.width = '0px';
			me._ht_node_custom_image3.ggSubElement.style.height = '0px';
			me._ht_node_custom_image3.ggSubElement.src='';
			me._ht_node_custom_image3.ggSubElement.src=me._ht_node_custom_image3.ggText;
		}
		el.ggUpdateImageTranslation = function() {
			if (me._ht_node_custom_image3.ggText != player._(me._ht_node_custom_image3.ggText_untranslated)) {
				me._ht_node_custom_image3.ggText = player._(me._ht_node_custom_image3.ggText_untranslated);
				me._ht_node_custom_image3.ggUpdateImage()
			}
		}
		if ((hotspot) && (hotspot.customimage)) {
			el.ggText=el.ggText_untranslated=hotspot.customimage;
			els.setAttribute('src', hotspot.customimage);
			els.style.width=hotspot.customimagewidth + 'px';
			els.style.height=hotspot.customimageheight + 'px';
			me.ggUse3d = hotspot.use3D;
			me.gg3dDistance = hotspot.distance3D;
		}
		els['ondragstart']=function() { return false; };
		player.checkLoaded.push(els);
		el.ggUpdateText();
		el.ggId="ht_node_custom_image";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_external ";
		el.ggType='external';
		hs ='';
		hs+='border : 0px solid #000000;';
		hs+='cursor : pointer;';
		hs+='height : 100%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='visibility : hidden;';
		hs+='width : 100%;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_custom_image3.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._ht_node_custom_image3.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_custom_image3'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_custom_image3.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_custom_image3.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_custom_image3.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image3.ggCurrentLogicStateScaling == 0) {
					me._ht_node_custom_image3.ggParameter.sx = 1.04;
					me._ht_node_custom_image3.ggParameter.sy = 1.04;
					me._ht_node_custom_image3.style.transform=parameterToTransform(me._ht_node_custom_image3.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image3);}, 250);
				}
				else {
					me._ht_node_custom_image3.ggParameter.sx = 1;
					me._ht_node_custom_image3.ggParameter.sy = 1;
					me._ht_node_custom_image3.style.transform=parameterToTransform(me._ht_node_custom_image3.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image3);}, 250);
				}
			}
		}
		me._ht_node_custom_image3.logicBlock_scaling();
		me._ht_node_custom_image3.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((me.hotspot.customimage != ""))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._ht_node_custom_image3.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._ht_node_custom_image3.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._ht_node_custom_image3.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image3.ggCurrentLogicStateVisible == 0) {
					me._ht_node_custom_image3.style.visibility=(Number(me._ht_node_custom_image3.style.opacity)>0||!me._ht_node_custom_image3.style.opacity)?'inherit':'hidden';
					me._ht_node_custom_image3.ggSubElement.src=me._ht_node_custom_image3.ggText;
					me._ht_node_custom_image3.ggVisible=true;
				}
				else {
					me._ht_node_custom_image3.style.visibility="hidden";
					me._ht_node_custom_image3.ggSubElement.src='';
					me._ht_node_custom_image3.ggVisible=false;
				}
			}
		}
		me._ht_node_custom_image3.logicBlock_visible();
		me._ht_node_custom_image3.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_custom_image3']=true;
			me._ht_node_custom_image3.logicBlock_scaling();
		}
		me._ht_node_custom_image3.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_custom_image3']=false;
			me._ht_node_custom_image3.logicBlock_scaling();
		}
		me._ht_node_custom_image3.ggUpdatePosition=function (useTransition) {
			var parentWidth = me._ht_node_custom_image3.clientWidth;
			var parentHeight = me._ht_node_custom_image3.clientHeight;
			var img = me._ht_node_custom_image3__img;
			var aspectRatioDiv = me._ht_node_custom_image3.clientWidth / me._ht_node_custom_image3.clientHeight;
			var aspectRatioImg = img.naturalWidth / img.naturalHeight;
			var currentWidth,currentHeight;
			if ((hotspot) && (hotspot.customimage)) {
				currentWidth  = hotspot.customimagewidth;
				currentHeight = hotspot.customimageheight;
			}
			if (!me._ht_node_custom_image3.ggScrollbars || currentWidth < me._ht_node_custom_image3.clientWidth) {
				img.style.right='';
				img.style.left='50%';
				img.style.marginLeft='-' + currentWidth/2 + 'px';
			} else {
				img.style.right='';
				img.style.left='0px';
				img.style.marginLeft='0px';
				me._ht_node_custom_image3.scrollLeft=currentWidth / 2 - me._ht_node_custom_image3.clientWidth / 2;
			}
			if (!me._ht_node_custom_image3.ggScrollbars || currentHeight < me._ht_node_custom_image3.clientHeight) {
				img.style.bottom='';
				img.style.top='0px';
			} else {
				img.style.bottom='';
				img.style.top='0px';
				img.style.marginTop='0px';
			}
		}
		me._box_ui_location_project.appendChild(me._ht_node_custom_image3);
		el=me._txthotspot2=document.createElement('div');
		els=me._txthotspot2__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot";
		el.ggDx=0;
		el.ggDy=-150;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text ";
		el.ggType='text';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 5px;';
		hs+='color : rgba(255,255,255,1);';
		hs+='height : 60px;';
		hs+='left : calc(50% - ((200px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((60px + 0px) / 2) - 150px);';
		hs+='visibility : inherit;';
		hs+='width : 200px;';
		hs+='pointer-events:none;';
		hs+='text-shadow: 0px 2px 3px rgba(0,0,0,0.6);';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		hs ='';
		hs += 'box-sizing: border-box;';
		hs+='width: 100%;';
		hs+='height: auto;';
		hs+='max-height: 100%;';
		hs+='pointer-events: none;';
		hs+='text-align: center;';
		hs+='position: absolute;';
		hs+='top: 50%;';
		hs+='transform: translate(0, -50%);';
		hs+='white-space: pre-line;';
		hs+='padding: 1px 1px 1px 1px;';
		hs+='overflow: hidden;';
		els.setAttribute('style',hs);
		me._txthotspot2.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.title)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._txthotspot2.ggUpdateText();
		player.addListener('changenode', function() {
			me._txthotspot2.ggUpdateText();
		});
		el.appendChild(els);
		me._txthotspot2.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._txthotspot2.ggUpdatePosition=function (useTransition) {
		}
		me._box_ui_location_project.appendChild(me._txthotspot2);
		me._box_ui_location_project.logicBlock_visible();
		me.elementMouseOver['box_ui_location_project']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image3.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image3.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image3.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image3.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image3.logicBlock_scaling();
		me._ht_node_custom_image3.logicBlock_visible();
		me.elementMouseOver['ht_node_custom_image3']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image3.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._box_ui_location_project.logicBlock_visible();
				me._ht_node_custom_image3.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._box_ui_location_project.logicBlock_visible();
				me._ht_node_custom_image3.logicBlock_visible();
			};
			me.ggEvent_varchanged_vis_skin=function() {
				me._box_ui_location_project.logicBlock_visible();
			};
			me.__div = me._box_ui_location_project;
	};
	function SkinHotspotClass_door_icon_project(parentScope,hotspot) {
		var me=this;
		var flag=false;
		var hs='';
		me.parentScope=parentScope;
		me.hotspot=hotspot;
		var nodeId=String(hotspot.url);
		nodeId=(nodeId.charAt(0)=='{')?nodeId.substr(1, nodeId.length - 2):''; // }
		me.ggUserdata=skin.player.getNodeUserdata(nodeId);
		me.elementMouseDown={};
		me.elementMouseOver={};
		me.findElements=function(id,regex) {
			return skin.findElements(id,regex);
		}
		el=me._door_icon_project=document.createElement('div');
		el.ggId="door_icon_project";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_hotspot ";
		el.ggType='hotspot';
		hs ='';
		hs+='z-index: -1;';
		hs+='cursor : pointer;';
		hs+='height : 0px;';
		hs+='left : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='visibility : inherit;';
		hs+='width : 0px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._door_icon_project.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			if (me.hotspot.url!='' && me.hotspot.url.charAt(0)=='{') { // }
				return me.hotspot.url.substr(1, me.hotspot.url.length - 2);
			} else {
				if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
					return this.parentNode.ggElementNodeId();
				} else {
					return player.getCurrentNode();
				}
			}
		}
		me._door_icon_project.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((player.getVariableValue('vis_skin') == true))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._door_icon_project.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._door_icon_project.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._door_icon_project.style.transition='';
				if (me._door_icon_project.ggCurrentLogicStateVisible == 0) {
					me._door_icon_project.style.visibility=(Number(me._door_icon_project.style.opacity)>0||!me._door_icon_project.style.opacity)?'inherit':'hidden';
					me._door_icon_project.ggVisible=true;
				}
				else {
					me._door_icon_project.style.visibility=(Number(me._door_icon_project.style.opacity)>0||!me._door_icon_project.style.opacity)?'inherit':'hidden';
					me._door_icon_project.ggVisible=true;
				}
			}
		}
		me._door_icon_project.logicBlock_visible();
		me._door_icon_project.onclick=function (e) {
			player.openNext(player._(me.hotspot.url),player._(me.hotspot.target));
			var list=me.findElements("hotspot-360-out",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			player.triggerEvent('hsproxyclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._door_icon_project.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._door_icon_project.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['door_icon_project']=true;
			me._rectangle_1_10.logicBlock_alpha();
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._door_icon_project.onmouseleave=function (e) {
			me.elementMouseOver['door_icon_project']=false;
			me._rectangle_1_10.logicBlock_alpha();
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me._door_icon_project.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_custom_image4=document.createElement('div');
		els=me._ht_node_custom_image4__img=document.createElement('img');
		els.className='ggskin ggskin_external';
		hs ='';
		hs += 'position: absolute;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.onload=function() {me._ht_node_custom_image4.ggUpdatePosition();}
		el.appendChild(els);
		el.ggSubElement = els;
		hs ='';
		el.ggAltText="";
		el.ggScrollbars=false;
		el.ggUpdateText = function() {
			me._ht_node_custom_image4.ggSubElement.setAttribute('alt', player._(me._ht_node_custom_image4.ggAltText));
			me._ht_node_custom_image4.ggUpdateImageTranslation();
		}
		el.ggSetImage = function(img) {
			me._ht_node_custom_image4.ggText_untranslated = img;
			me._ht_node_custom_image4.ggUpdateImageTranslation();
		}
		el.ggUpdateImage = function() {
			me._ht_node_custom_image4.ggSubElement.style.width = '0px';
			me._ht_node_custom_image4.ggSubElement.style.height = '0px';
			me._ht_node_custom_image4.ggSubElement.src='';
			me._ht_node_custom_image4.ggSubElement.src=me._ht_node_custom_image4.ggText;
		}
		el.ggUpdateImageTranslation = function() {
			if (me._ht_node_custom_image4.ggText != player._(me._ht_node_custom_image4.ggText_untranslated)) {
				me._ht_node_custom_image4.ggText = player._(me._ht_node_custom_image4.ggText_untranslated);
				me._ht_node_custom_image4.ggUpdateImage()
			}
		}
		if ((hotspot) && (hotspot.customimage)) {
			el.ggText=el.ggText_untranslated=hotspot.customimage;
			els.setAttribute('src', hotspot.customimage);
			els.style.width=hotspot.customimagewidth + 'px';
			els.style.height=hotspot.customimageheight + 'px';
			me.ggUse3d = hotspot.use3D;
			me.gg3dDistance = hotspot.distance3D;
		}
		els['ondragstart']=function() { return false; };
		player.checkLoaded.push(els);
		el.ggUpdateText();
		el.ggId="ht_node_custom_image";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_external ";
		el.ggType='external';
		hs ='';
		hs+='border : 0px solid #000000;';
		hs+='cursor : pointer;';
		hs+='height : 100%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='visibility : hidden;';
		hs+='width : 100%;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_custom_image4.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._ht_node_custom_image4.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_custom_image4'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_custom_image4.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_custom_image4.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_custom_image4.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image4.ggCurrentLogicStateScaling == 0) {
					me._ht_node_custom_image4.ggParameter.sx = 1.04;
					me._ht_node_custom_image4.ggParameter.sy = 1.04;
					me._ht_node_custom_image4.style.transform=parameterToTransform(me._ht_node_custom_image4.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image4);}, 250);
				}
				else {
					me._ht_node_custom_image4.ggParameter.sx = 1;
					me._ht_node_custom_image4.ggParameter.sy = 1;
					me._ht_node_custom_image4.style.transform=parameterToTransform(me._ht_node_custom_image4.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image4);}, 250);
				}
			}
		}
		me._ht_node_custom_image4.logicBlock_scaling();
		me._ht_node_custom_image4.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((me.hotspot.customimage != ""))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._ht_node_custom_image4.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._ht_node_custom_image4.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._ht_node_custom_image4.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image4.ggCurrentLogicStateVisible == 0) {
					me._ht_node_custom_image4.style.visibility=(Number(me._ht_node_custom_image4.style.opacity)>0||!me._ht_node_custom_image4.style.opacity)?'inherit':'hidden';
					me._ht_node_custom_image4.ggSubElement.src=me._ht_node_custom_image4.ggText;
					me._ht_node_custom_image4.ggVisible=true;
				}
				else {
					me._ht_node_custom_image4.style.visibility="hidden";
					me._ht_node_custom_image4.ggSubElement.src='';
					me._ht_node_custom_image4.ggVisible=false;
				}
			}
		}
		me._ht_node_custom_image4.logicBlock_visible();
		me._ht_node_custom_image4.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_custom_image4']=true;
			me._ht_node_custom_image4.logicBlock_scaling();
		}
		me._ht_node_custom_image4.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_custom_image4']=false;
			me._ht_node_custom_image4.logicBlock_scaling();
		}
		me._ht_node_custom_image4.ggUpdatePosition=function (useTransition) {
			var parentWidth = me._ht_node_custom_image4.clientWidth;
			var parentHeight = me._ht_node_custom_image4.clientHeight;
			var img = me._ht_node_custom_image4__img;
			var aspectRatioDiv = me._ht_node_custom_image4.clientWidth / me._ht_node_custom_image4.clientHeight;
			var aspectRatioImg = img.naturalWidth / img.naturalHeight;
			var currentWidth,currentHeight;
			if ((hotspot) && (hotspot.customimage)) {
				currentWidth  = hotspot.customimagewidth;
				currentHeight = hotspot.customimageheight;
			}
			if (!me._ht_node_custom_image4.ggScrollbars || currentWidth < me._ht_node_custom_image4.clientWidth) {
				img.style.right='';
				img.style.left='50%';
				img.style.marginLeft='-' + currentWidth/2 + 'px';
			} else {
				img.style.right='';
				img.style.left='0px';
				img.style.marginLeft='0px';
				me._ht_node_custom_image4.scrollLeft=currentWidth / 2 - me._ht_node_custom_image4.clientWidth / 2;
			}
			if (!me._ht_node_custom_image4.ggScrollbars || currentHeight < me._ht_node_custom_image4.clientHeight) {
				img.style.bottom='';
				img.style.top='0px';
			} else {
				img.style.bottom='';
				img.style.top='0px';
				img.style.marginTop='0px';
			}
		}
		me._door_icon_project.appendChild(me._ht_node_custom_image4);
		el=me._rectangle_1_10=document.createElement('div');
		el.ggId="Rectangle 1_1";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:0.35,sy:0.35,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_rectangle box-shadow: 0 1px 3px rgba(0 0 0 0.12) 0 1px 2px rgba(0 0 0 0.24);";
		el.ggType='rectangle';
		hs ='';
		hs+='background : #ffffff;';
		hs+='border : 1px solid #949494;';
		hs+='border-radius : 10px;';
		hs+='height : 159px;';
		hs+='left : calc(50% - ((278px + 2px) / 2) + 0px);';
		hs+='opacity : 0;';
		hs+='overflow : hidden;';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((159px + 2px) / 2) + 0px);';
		hs+='visibility : hidden;';
		hs+='width : 278px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		el.style.transform=parameterToTransform(el.ggParameter);
		me._rectangle_1_10.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._rectangle_1_10.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['rectangle_1_10'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._rectangle_1_10.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._rectangle_1_10.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._rectangle_1_10.style.transition='transform 100ms ease 0ms, opacity 500ms ease 0ms';
				if (me._rectangle_1_10.ggCurrentLogicStateScaling == 0) {
					me._rectangle_1_10.ggParameter.sx = 0.5;
					me._rectangle_1_10.ggParameter.sy = 0.5;
					me._rectangle_1_10.style.transform=parameterToTransform(me._rectangle_1_10.ggParameter);
					setTimeout(function() {skin.updateSize(me._rectangle_1_10);}, 150);
				}
				else {
					me._rectangle_1_10.ggParameter.sx = 0.35;
					me._rectangle_1_10.ggParameter.sy = 0.35;
					me._rectangle_1_10.style.transform=parameterToTransform(me._rectangle_1_10.ggParameter);
					setTimeout(function() {skin.updateSize(me._rectangle_1_10);}, 150);
				}
			}
		}
		me._rectangle_1_10.logicBlock_scaling();
		me._rectangle_1_10.logicBlock_alpha = function() {
			var newLogicStateAlpha;
			if (
				((me.elementMouseOver['door_icon_project'] == true))
			)
			{
				newLogicStateAlpha = 0;
			}
			else {
				newLogicStateAlpha = -1;
			}
			if (me._rectangle_1_10.ggCurrentLogicStateAlpha != newLogicStateAlpha) {
				me._rectangle_1_10.ggCurrentLogicStateAlpha = newLogicStateAlpha;
				me._rectangle_1_10.style.transition='transform 100ms ease 0ms, opacity 500ms ease 0ms';
				if (me._rectangle_1_10.ggCurrentLogicStateAlpha == 0) {
					me._rectangle_1_10.style.visibility=me._rectangle_1_10.ggVisible?'inherit':'hidden';
					me._rectangle_1_10.style.opacity=1;
				}
				else {
					setTimeout(function() { if (me._rectangle_1_10.style.opacity == 0.0) { me._rectangle_1_10.style.visibility="hidden"; } }, 505);
					me._rectangle_1_10.style.opacity=0;
				}
			}
		}
		me._rectangle_1_10.logicBlock_alpha();
		me._rectangle_1_10.onmouseenter=function (e) {
			me.elementMouseOver['rectangle_1_10']=true;
			me._rectangle_1_10.logicBlock_scaling();
		}
		me._rectangle_1_10.onmouseleave=function (e) {
			me.elementMouseOver['rectangle_1_10']=false;
			me._rectangle_1_10.logicBlock_scaling();
		}
		me._rectangle_1_10.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_thumb_11=document.createElement('div');
		els=me._ht_node_thumb_11__img=document.createElement('img');
		els.className='ggskin ggskin_nodeimage';
		els.loading = 'lazy';
		els.setAttribute('src',basePath + "images/ht_node_thumb_1_" + player.getCurrentNode() + "_" + me.hotspot.id + ".webp");
		el.ggNodeId=nodeId;
		hs ='';
		hs += 'position: absolute;top: 0px;left: 0px;width: 100%;height: 100%;-webkit-user-drag:none;pointer-events:none;;';
		hs+='border-radius: 10px;';
		els.setAttribute('style', hs);
		els.className='ggskin ggskin_nodeimage';
		els['ondragstart']=function() { return false; };
		el.appendChild(els);
		el.ggSubElement = els;
		el.ggId="ht_node_thumb_1";
		el.ggDx=1;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_nodeimage test-css";
		el.ggType='nodeimage';
		hs ='';
		hs+='cursor : pointer;';
		hs+='height : 138px;';
		hs+='left : calc(50% - ((250px + 0px) / 2) + 1px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((138px + 0px) / 2) + 0px);';
		hs+='visibility : inherit;';
		hs+='width : 250px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_thumb_11.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			return this.ggNodeId;
		}
		me._ht_node_thumb_11.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_thumb_11'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_thumb_11.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_thumb_11.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_thumb_11.style.transition='transform 100ms ease 0ms';
				if (me._ht_node_thumb_11.ggCurrentLogicStateScaling == 0) {
					me._ht_node_thumb_11.ggParameter.sx = 1.05;
					me._ht_node_thumb_11.ggParameter.sy = 1.05;
					me._ht_node_thumb_11.style.transform=parameterToTransform(me._ht_node_thumb_11.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_thumb_11);}, 150);
				}
				else {
					me._ht_node_thumb_11.ggParameter.sx = 1;
					me._ht_node_thumb_11.ggParameter.sy = 1;
					me._ht_node_thumb_11.style.transform=parameterToTransform(me._ht_node_thumb_11.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_thumb_11);}, 150);
				}
			}
		}
		me._ht_node_thumb_11.logicBlock_scaling();
		me._ht_node_thumb_11.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_thumb_11']=true;
			me._ht_node_thumb_11.logicBlock_scaling();
		}
		me._ht_node_thumb_11.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_thumb_11']=false;
			me._ht_node_thumb_11.logicBlock_scaling();
		}
		me._ht_node_thumb_11.ggUpdatePosition=function (useTransition) {
		}
		me._rectangle_1_10.appendChild(me._ht_node_thumb_11);
		me._door_icon_project.appendChild(me._rectangle_1_10);
		el=me._txthotspot3=document.createElement('div');
		els=me._txthotspot3__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot";
		el.ggDx=0;
		el.ggDy=-70;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text ";
		el.ggType='text';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 5px;';
		hs+='color : rgba(255,255,255,1);';
		hs+='height : 60px;';
		hs+='left : calc(50% - ((200px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((60px + 0px) / 2) - 70px);';
		hs+='visibility : inherit;';
		hs+='width : 200px;';
		hs+='pointer-events:none;';
		hs+='text-shadow: 0px 2px 3px rgba(0,0,0,0.6);';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		hs ='';
		hs += 'box-sizing: border-box;';
		hs+='width: 100%;';
		hs+='height: auto;';
		hs+='max-height: 100%;';
		hs+='pointer-events: none;';
		hs+='font-size: 19px;';
		hs+='font-weight: inherit;';
		hs+='text-align: center;';
		hs+='position: absolute;';
		hs+='top: 50%;';
		hs+='transform: translate(0, -50%);';
		hs+='white-space: pre-line;';
		hs+='padding: 1px 1px 1px 1px;';
		hs+='overflow: hidden;';
		els.setAttribute('style',hs);
		me._txthotspot3.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.title)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._txthotspot3.ggUpdateText();
		player.addListener('changenode', function() {
			me._txthotspot3.ggUpdateText();
		});
		el.appendChild(els);
		me._txthotspot3.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._txthotspot3.ggUpdatePosition=function (useTransition) {
		}
		me._door_icon_project.appendChild(me._txthotspot3);
		me._door_icon_project.logicBlock_visible();
		me.elementMouseOver['door_icon_project']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image4.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image4.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image4.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image4.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image4.logicBlock_scaling();
		me._ht_node_custom_image4.logicBlock_visible();
		me.elementMouseOver['ht_node_custom_image4']=false;
		me._rectangle_1_10.logicBlock_scaling();
		me._rectangle_1_10.logicBlock_alpha();
		me.elementMouseOver['rectangle_1_10']=false;
		me._ht_node_thumb_11.logicBlock_scaling();
		me.elementMouseOver['ht_node_thumb_11']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image4.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._door_icon_project.logicBlock_visible();
				me._ht_node_custom_image4.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._door_icon_project.logicBlock_visible();
				me._ht_node_custom_image4.logicBlock_visible();
			};
			me.ggEvent_varchanged_vis_skin=function() {
				me._door_icon_project.logicBlock_visible();
			};
			me.__div = me._door_icon_project;
	};
	function SkinHotspotClass_hotspot_darat_project(parentScope,hotspot) {
		var me=this;
		var flag=false;
		var hs='';
		me.parentScope=parentScope;
		me.hotspot=hotspot;
		var nodeId=String(hotspot.url);
		nodeId=(nodeId.charAt(0)=='{')?nodeId.substr(1, nodeId.length - 2):''; // }
		me.ggUserdata=skin.player.getNodeUserdata(nodeId);
		me.elementMouseDown={};
		me.elementMouseOver={};
		me.findElements=function(id,regex) {
			return skin.findElements(id,regex);
		}
		el=me._hotspot_darat_project=document.createElement('div');
		el.ggId="hotspot_darat_project";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_hotspot ";
		el.ggType='hotspot';
		hs ='';
		hs+='z-index: -1;';
		hs+='cursor : pointer;';
		hs+='height : 0px;';
		hs+='left : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='visibility : inherit;';
		hs+='width : 0px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._hotspot_darat_project.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			if (me.hotspot.url!='' && me.hotspot.url.charAt(0)=='{') { // }
				return me.hotspot.url.substr(1, me.hotspot.url.length - 2);
			} else {
				if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
					return this.parentNode.ggElementNodeId();
				} else {
					return player.getCurrentNode();
				}
			}
		}
		me._hotspot_darat_project.onclick=function (e) {
			player.openNext(player._(me.hotspot.url),player._(me.hotspot.target));
			var list=me.findElements("hotspot-360-out",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			player.triggerEvent('hsproxyclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._hotspot_darat_project.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._hotspot_darat_project.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['hotspot_darat_project']=true;
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._hotspot_darat_project.onmouseleave=function (e) {
			me.elementMouseOver['hotspot_darat_project']=false;
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me._hotspot_darat_project.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_custom_image5=document.createElement('div');
		els=me._ht_node_custom_image5__img=document.createElement('img');
		els.className='ggskin ggskin_external';
		hs ='';
		hs += 'position: absolute;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.onload=function() {me._ht_node_custom_image5.ggUpdatePosition();}
		el.appendChild(els);
		el.ggSubElement = els;
		hs ='';
		el.ggAltText="";
		el.ggScrollbars=false;
		el.ggUpdateText = function() {
			me._ht_node_custom_image5.ggSubElement.setAttribute('alt', player._(me._ht_node_custom_image5.ggAltText));
			me._ht_node_custom_image5.ggUpdateImageTranslation();
		}
		el.ggSetImage = function(img) {
			me._ht_node_custom_image5.ggText_untranslated = img;
			me._ht_node_custom_image5.ggUpdateImageTranslation();
		}
		el.ggUpdateImage = function() {
			me._ht_node_custom_image5.ggSubElement.style.width = '0px';
			me._ht_node_custom_image5.ggSubElement.style.height = '0px';
			me._ht_node_custom_image5.ggSubElement.src='';
			me._ht_node_custom_image5.ggSubElement.src=me._ht_node_custom_image5.ggText;
		}
		el.ggUpdateImageTranslation = function() {
			if (me._ht_node_custom_image5.ggText != player._(me._ht_node_custom_image5.ggText_untranslated)) {
				me._ht_node_custom_image5.ggText = player._(me._ht_node_custom_image5.ggText_untranslated);
				me._ht_node_custom_image5.ggUpdateImage()
			}
		}
		if ((hotspot) && (hotspot.customimage)) {
			el.ggText=el.ggText_untranslated=hotspot.customimage;
			els.setAttribute('src', hotspot.customimage);
			els.style.width=hotspot.customimagewidth + 'px';
			els.style.height=hotspot.customimageheight + 'px';
			me.ggUse3d = hotspot.use3D;
			me.gg3dDistance = hotspot.distance3D;
		}
		els['ondragstart']=function() { return false; };
		player.checkLoaded.push(els);
		el.ggUpdateText();
		el.ggId="ht_node_custom_image";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_external ";
		el.ggType='external';
		hs ='';
		hs+='border : 0px solid #000000;';
		hs+='cursor : pointer;';
		hs+='height : 100%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='visibility : hidden;';
		hs+='width : 100%;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_custom_image5.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._ht_node_custom_image5.logicBlock_visible = function() {
			var newLogicStateVisible;
			if (
				((me.hotspot.customimage != ""))
			)
			{
				newLogicStateVisible = 0;
			}
			else {
				newLogicStateVisible = -1;
			}
			if (me._ht_node_custom_image5.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._ht_node_custom_image5.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._ht_node_custom_image5.style.transition='';
				if (me._ht_node_custom_image5.ggCurrentLogicStateVisible == 0) {
					me._ht_node_custom_image5.style.visibility=(Number(me._ht_node_custom_image5.style.opacity)>0||!me._ht_node_custom_image5.style.opacity)?'inherit':'hidden';
					me._ht_node_custom_image5.ggSubElement.src=me._ht_node_custom_image5.ggText;
					me._ht_node_custom_image5.ggVisible=true;
				}
				else {
					me._ht_node_custom_image5.style.visibility="hidden";
					me._ht_node_custom_image5.ggSubElement.src='';
					me._ht_node_custom_image5.ggVisible=false;
				}
			}
		}
		me._ht_node_custom_image5.logicBlock_visible();
		me._ht_node_custom_image5.ggUpdatePosition=function (useTransition) {
			var parentWidth = me._ht_node_custom_image5.clientWidth;
			var parentHeight = me._ht_node_custom_image5.clientHeight;
			var img = me._ht_node_custom_image5__img;
			var aspectRatioDiv = me._ht_node_custom_image5.clientWidth / me._ht_node_custom_image5.clientHeight;
			var aspectRatioImg = img.naturalWidth / img.naturalHeight;
			var currentWidth,currentHeight;
			if ((hotspot) && (hotspot.customimage)) {
				currentWidth  = hotspot.customimagewidth;
				currentHeight = hotspot.customimageheight;
			}
			if (!me._ht_node_custom_image5.ggScrollbars || currentWidth < me._ht_node_custom_image5.clientWidth) {
				img.style.right='';
				img.style.left='50%';
				img.style.marginLeft='-' + currentWidth/2 + 'px';
			} else {
				img.style.right='';
				img.style.left='0px';
				img.style.marginLeft='0px';
				me._ht_node_custom_image5.scrollLeft=currentWidth / 2 - me._ht_node_custom_image5.clientWidth / 2;
			}
			if (!me._ht_node_custom_image5.ggScrollbars || currentHeight < me._ht_node_custom_image5.clientHeight) {
				img.style.bottom='';
				img.style.top='0px';
			} else {
				img.style.bottom='';
				img.style.top='0px';
				img.style.marginTop='0px';
			}
		}
		me._hotspot_darat_project.appendChild(me._ht_node_custom_image5);
		el=me._rectangle_10=document.createElement('div');
		el.ggId="Rectangle 1";
		el.ggDx=0;
		el.ggDy=-26;
		el.ggParameter={ rx:0,ry:0,a:0,sx:0.35,sy:0.35,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_rectangle box-shadow: 0 1px 3px rgba(0 0 0 0.12) 0 1px 2px rgba(0 0 0 0.24);";
		el.ggType='rectangle';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 10px;';
		hs+='cursor : pointer;';
		hs+='height : 159px;';
		hs+='left : calc(50% - ((278px + 0px) / 2) + 0px);';
		hs+='overflow : hidden;';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((159px + 0px) / 2) - 26px);';
		hs+='visibility : inherit;';
		hs+='width : 278px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		el.style.transform=parameterToTransform(el.ggParameter);
		me._rectangle_10.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._rectangle_10.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['rectangle_10'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._rectangle_10.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._rectangle_10.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._rectangle_10.style.transition='transform 100ms ease 0ms';
				if (me._rectangle_10.ggCurrentLogicStateScaling == 0) {
					me._rectangle_10.ggParameter.sx = 0.39;
					me._rectangle_10.ggParameter.sy = 0.39;
					me._rectangle_10.style.transform=parameterToTransform(me._rectangle_10.ggParameter);
					setTimeout(function() {skin.updateSize(me._rectangle_10);}, 150);
				}
				else {
					me._rectangle_10.ggParameter.sx = 0.35;
					me._rectangle_10.ggParameter.sy = 0.35;
					me._rectangle_10.style.transform=parameterToTransform(me._rectangle_10.ggParameter);
					setTimeout(function() {skin.updateSize(me._rectangle_10);}, 150);
				}
			}
		}
		me._rectangle_10.logicBlock_scaling();
		me._rectangle_10.onmouseenter=function (e) {
			me.elementMouseOver['rectangle_10']=true;
			me._rectangle_10.logicBlock_scaling();
		}
		me._rectangle_10.onmouseleave=function (e) {
			me.elementMouseOver['rectangle_10']=false;
			me._rectangle_10.logicBlock_scaling();
		}
		me._rectangle_10.ggUpdatePosition=function (useTransition) {
		}
		el=me._ht_node_thumb_12=document.createElement('div');
		els=me._ht_node_thumb_12__img=document.createElement('img');
		els.className='ggskin ggskin_nodeimage';
		els.loading = 'lazy';
		els.setAttribute('src',basePath + "images/ht_node_thumb_1_" + player.getCurrentNode() + "_" + me.hotspot.id + ".webp");
		el.ggNodeId=nodeId;
		hs ='';
		hs += 'position: absolute;top: 0px;left: 0px;width: 100%;height: 100%;-webkit-user-drag:none;pointer-events:none;;';
		hs+='border-radius: 10px;';
		els.setAttribute('style', hs);
		els.className='ggskin ggskin_nodeimage';
		els['ondragstart']=function() { return false; };
		el.appendChild(els);
		el.ggSubElement = els;
		el.ggId="ht_node_thumb_1";
		el.ggDx=1;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_nodeimage test-css";
		el.ggType='nodeimage';
		hs ='';
		hs+='cursor : pointer;';
		hs+='height : 138px;';
		hs+='left : calc(50% - ((250px + 0px) / 2) + 1px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((138px + 0px) / 2) + 0px);';
		hs+='visibility : inherit;';
		hs+='width : 250px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._ht_node_thumb_12.ggIsActive=function() {
			return player.getCurrentNode()==this.ggElementNodeId();
		}
		el.ggElementNodeId=function() {
			return this.ggNodeId;
		}
		me._ht_node_thumb_12.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_thumb_12'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_thumb_12.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_thumb_12.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_thumb_12.style.transition='transform 100ms ease 0ms';
				if (me._ht_node_thumb_12.ggCurrentLogicStateScaling == 0) {
					me._ht_node_thumb_12.ggParameter.sx = 1.05;
					me._ht_node_thumb_12.ggParameter.sy = 1.05;
					me._ht_node_thumb_12.style.transform=parameterToTransform(me._ht_node_thumb_12.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_thumb_12);}, 150);
				}
				else {
					me._ht_node_thumb_12.ggParameter.sx = 1;
					me._ht_node_thumb_12.ggParameter.sy = 1;
					me._ht_node_thumb_12.style.transform=parameterToTransform(me._ht_node_thumb_12.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_thumb_12);}, 150);
				}
			}
		}
		me._ht_node_thumb_12.logicBlock_scaling();
		me._ht_node_thumb_12.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_thumb_12']=true;
			me._ht_node_thumb_12.logicBlock_scaling();
		}
		me._ht_node_thumb_12.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_thumb_12']=false;
			me._ht_node_thumb_12.logicBlock_scaling();
		}
		me._ht_node_thumb_12.ggUpdatePosition=function (useTransition) {
		}
		me._rectangle_10.appendChild(me._ht_node_thumb_12);
		el=me._image_10=document.createElement('div');
		els=me._image_10__img=document.createElement('img');
		els.className='ggskin ggskin_image_10';
		hs='data:image/webp;base64,UklGRkYEAABXRUJQVlA4WAoAAAAQAAAAbAEA0wAAQUxQSBYDAAARoCv/nyJb+T9CP0I/wa3OLzIxOhkOg8OJBneYEKfrRic61Whki0vUuEPjDl1EFNH/Sv4NplfOif7oRsQEyIZdZ71ycPuYMWDNUzgAPip2XEe3Py5hzDrtx6jYcwmbcguWljh01jum2oJxM67QrKMXGw5JG6RNuMJcRzFklxqk9Vxhnp3YcldnxLUS8yjm7MqMfo2e+SAG7cpM3UpOZ0lM2hWAxUoTQBaj9grQreAUUG9VMs7yCiPAJHZdAPyyAqgzrH4Wl3iAJJZdgbpknAXTigC+lQAV0+5mfSsD2bZkNrUAJuMqQLKzDOSt/7b+2/rvf/VVIBhXBFIrLPIgxu1STq7175GdC866gvNORJy4oeZ6hrcsJ2Mq9bQg0iXPTr2z3lY6uyqOmPfylD'+
			'rHV+eKlwu+xdtVrTeKc2e8QScLqH4BRew6Qg0R1IlX7k7nosGwnBIXVyidiIT4FGkRxLJd3KUuepl37zz9cie27crek6O0yuFiXV7PSVPL7dA745KTia4l9/5+tFjX8O1xdQ0HYF0AoXVv+ewK6xrLh3dKe4LJujqYtv7b+m/rv63//mlwTv5+x7pOyl/ttAJ//PyVdXGiHPMNyYp5JeVP1wp6Wm9dXm/spOnOqVd443JX5euWdPU0OuPqCDq1/JG6643L7eqRvjGReEFH0xo08VWNIjKoDjt7x+gNK6Cxv/cYk4iio5sqxbAKGmWsqHQ8NYWrToq7eLvihXu78dzxbu3CMCye5hyVIdhVDPUB7i5dCCLuUJ3Ui237OuohJ/8u6YKlDVCsK1Q0tBQYjCsBixbAZFwZyFv/bf239d//6qtAMi4FplYGim05VokAzrT6'+
			'WdfqZ4NppZm03SxbllNgsUQWAN6wJoBhWT/LduUUULdMKkBvVhEgyorDTJ1R9QDqVpEMUJxJBZ1NsrJXgOIMKihAkTWHGcWbU68A6teROEMHW3KRZpD10wxyZ0hDpTnIJqcGlNGbUIhKUzvZbK8NQHOcbDdllpcgm/Z5iSVPsp9dNqbkZZ+7pGZUJy8HMYwpZzWdmnMcvGwSVlA4IAoBAABQFgCdASptAdQAPjEYi0QiIaEQFCggAwS0t3C7AI2gBPjRo0aNGjRo0aNGjRo0aNGjRo0aNGjRo0aNGjRo0aNGjRo0aNGjRovRtnLyZMmTJkyZLyO9MP9sBYX506dOnTp06dHffIGWTJkyZMmTJkyZMmTJkyZMmTHlhx9evXr169evXagPUAsL86dOnTp06dOjvvkDLJkyZMmTJkyZMmTJkyZMmTJkyZMmTJkyZMmTJk'+
			'yZMmTJkyZMmTHgAP7/58SAADp/qTTIW7r5kb/m7Vnpp/a7GaZuI+vX02yDleg7mNmLhx+qAACs/jAQ/GR569tmD1j6fxOgxcAnhe6omWHO8vX+BwAAAAAAAA==';
		els.setAttribute('src',hs);
		els.ggNormalSrc=hs;
		hs ='';
		hs += 'position: absolute;top: 0px;left: 0px;width: 100%;height: 100%;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.className='ggskin ggskin_image';
		els['ondragstart']=function() { return false; };
		player.checkLoaded.push(els);
		el.appendChild(els);
		el.ggSubElement = els;
		el.ggId="Image 1";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_image ";
		el.ggType='image';
		hs ='';
		hs+='height : 155px;';
		hs+='left : calc(50% - ((270px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((155px + 0px) / 2) + 0px);';
		hs+='visibility : inherit;';
		hs+='width : 270px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._image_10.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._image_10.ggUpdatePosition=function (useTransition) {
		}
		me._rectangle_10.appendChild(me._image_10);
		me._hotspot_darat_project.appendChild(me._rectangle_10);
		el=me._txthotspot4=document.createElement('div');
		els=me._txthotspot4__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot";
		el.ggDx=0;
		el.ggDy=-73;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text ";
		el.ggType='text';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 5px;';
		hs+='color : rgba(255,255,255,1);';
		hs+='cursor : pointer;';
		hs+='height : 60px;';
		hs+='left : calc(50% - ((200px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((60px + 0px) / 2) - 73px);';
		hs+='visibility : inherit;';
		hs+='width : 200px;';
		hs+='pointer-events:none;';
		hs+='text-shadow: 0px 2px 3px rgba(0,0,0,0.6);';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		hs ='';
		hs += 'box-sizing: border-box;';
		hs+='width: 100%;';
		hs+='height: auto;';
		hs+='max-height: 100%;';
		hs+='pointer-events: none;';
		hs+='text-align: center;';
		hs+='position: absolute;';
		hs+='top: 50%;';
		hs+='transform: translate(0, -50%);';
		hs+='white-space: pre-line;';
		hs+='padding: 1px 1px 1px 1px;';
		hs+='overflow: hidden;';
		els.setAttribute('style',hs);
		me._txthotspot4.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.title)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._txthotspot4.ggUpdateText();
		player.addListener('changenode', function() {
			me._txthotspot4.ggUpdateText();
		});
		el.appendChild(els);
		me._txthotspot4.ggIsActive=function() {
			if ((this.parentNode) && (this.parentNode.ggIsActive)) {
				return this.parentNode.ggIsActive();
			}
			return false;
		}
		el.ggElementNodeId=function() {
			if ((this.parentNode) && (this.parentNode.ggElementNodeId)) {
				return this.parentNode.ggElementNodeId();
			}
			return me.ggNodeId;
		}
		me._txthotspot4.ggUpdatePosition=function (useTransition) {
		}
		me._hotspot_darat_project.appendChild(me._txthotspot4);
		me.elementMouseOver['hotspot_darat_project']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image5.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image5.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image5.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image5.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image5.logicBlock_visible();
		me._rectangle_10.logicBlock_scaling();
		me.elementMouseOver['rectangle_10']=false;
		me._ht_node_thumb_12.logicBlock_scaling();
		me.elementMouseOver['ht_node_thumb_12']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image5.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image5.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image5.logicBlock_visible();
			};
			var hotspotArrow=document.createElement('div');
		hotspotArrow.setAttribute('aria-hidden','true');
		hotspotArrow.setAttribute('style','position:absolute;left:50%;bottom:-18px;margin-left:-14px;width:0;height:0;border-left:14px solid transparent;border-right:14px solid transparent;border-top:18px solid #fff;pointer-events:none;');
		me._txthotspot4.appendChild(hotspotArrow);
		// Monas: satu kartu dan satu skala untuk foto, judul, serta segitiga.
		// Jangan skala judul terpisah atau biarkan logic hover ekspor mengubah ukurannya.
		me._image_10.remove();
		me._ht_node_custom_image5.style.display='none';
		me._rectangle_10.appendChild(me._txthotspot4);
		me._rectangle_10.ggParameter.sx=0.4;
		me._rectangle_10.ggParameter.sy=0.4;
		me._rectangle_10.ggVisible=true;
		me._ht_node_thumb_12.ggVisible=true;
		me._txthotspot4.ggParameter.sx=1;
		me._txthotspot4.ggParameter.sy=1;
		me._rectangle_10.ggUpdatePosition=function() {
			me._rectangle_10.style.cssText='position:absolute;left:-139px;top:-103.5px;width:278px;height:207px;box-sizing:border-box;visibility:inherit;overflow:visible;transform:scale(.4);transform-origin:50% 50%;border:1px solid #fff;outline:6px solid #fff;outline-offset:0;border-radius:20px;background:#fff;box-shadow:0 12px 32px -12px rgba(0,0,0,.6);cursor:pointer;pointer-events:auto;';
		};
		me._ht_node_thumb_12.ggUpdatePosition=function() {
			me._ht_node_thumb_12.style.cssText='position:absolute;left:0;top:0;width:100%;height:159px;visibility:inherit;overflow:hidden;border-radius:19px 19px 0 0;transform:none;pointer-events:auto;';
			me._ht_node_thumb_12__img.style.cssText='display:block;width:100%;height:100%;object-fit:cover;border-radius:0;pointer-events:none;';
		};
		me._txthotspot4.ggUpdatePosition=function() {
			me._txthotspot4.style.cssText='position:absolute;left:0;top:159px;width:100%;height:48px;visibility:inherit;overflow:visible;transform:none;border:0;background:transparent;border-radius:0 0 19px 19px;color:#2e4b69;font-size:18px;font-weight:700;pointer-events:none;';
		};
		me._rectangle_10.logicBlock_scaling=me._rectangle_10.ggUpdatePosition;
		me._ht_node_thumb_12.logicBlock_scaling=me._ht_node_thumb_12.ggUpdatePosition;
		me._ht_node_thumb_12__img.loading='eager';
		me._rectangle_10.ggUpdatePosition();
		me._ht_node_thumb_12.ggUpdatePosition();
		me._txthotspot4.ggUpdatePosition();
		// Interaksi lokal: tidak mengubah transform/proyeksi yang dikelola player.
		var card=me._rectangle_10;
		var reducedMotion=window.matchMedia('(prefers-reduced-motion: reduce)');
		var idleAnimation=null;
		var hoverAnimation=null;
		var cardActive=false;
		card.tabIndex=0;
		card.setAttribute('role','link');
		card.setAttribute('aria-label','Jelajahi '+player._(me.hotspot.title));
		card.title='Klik untuk menjelajahi '+player._(me.hotspot.title);
		function animateCard(active) {
			var startTranslate=getComputedStyle(card).translate;
            if(!startTranslate || startTranslate==="none") startTranslate="0px 0px";
            cardActive=active;
			if(idleAnimation) { idleAnimation.cancel(); idleAnimation=null; }
			if(hoverAnimation) { hoverAnimation.cancel(); hoverAnimation=null; }
			if(!card.animate) return;
			hoverAnimation=card.animate([{translate:startTranslate},{translate:active && !reducedMotion.matches ? '0px -3px' : '0px 0px'}],{duration:reducedMotion.matches?0:200,easing:'ease-out',fill:'forwards'});
            if(!active && !reducedMotion.matches) {
				idleAnimation=card.animate([
					{boxShadow:'0 12px 32px -12px rgba(0,0,0,.6)'},
					{boxShadow:'0 12px 32px -12px rgba(0,0,0,.6), 0 0 0 10px rgba(188,213,235,.35)'},
					{boxShadow:'0 12px 32px -12px rgba(0,0,0,.6)'}
				],{duration:2800,iterations:Infinity});
			}
		}
		card.addEventListener('mouseenter',function(){animateCard(true);});
		card.addEventListener('mouseleave',function(){animateCard(document.activeElement===card);});
		card.addEventListener('focus',function(){animateCard(true);});
		card.addEventListener('blur',function(){animateCard(card.matches(':hover'));});
		card.addEventListener('keydown',function(e){
			if(e.key==='Enter') { e.preventDefault(); e.stopPropagation(); me._hotspot_darat_project.onclick(e); }
		});
		// Baca preferensi terbaru setiap interaksi tanpa listener global per hotspot.
		animateCard(false);
			me.__div = me._hotspot_darat_project;
	};
	me.addSkinHotspot=function(hotspot) {
		var hsinst = null;
			if (hotspot.skinid=='hotspot_darat_project') {
				hotspot.skinid = 'hotspot_darat_project';
				hsinst = new SkinHotspotClass_hotspot_darat_project(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		} else
			if (hotspot.skinid=='door_icon_project') {
				hotspot.skinid = 'door_icon_project';
				hsinst = new SkinHotspotClass_door_icon_project(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		} else
			if (hotspot.skinid=='box_ui_location_project') {
				hotspot.skinid = 'box_ui_location_project';
				hsinst = new SkinHotspotClass_box_ui_location_project(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		} else
			if (hotspot.skinid=='drone_icon_project') {
				hotspot.skinid = 'drone_icon_project';
				hsinst = new SkinHotspotClass_drone_icon_project(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		} else
			if (hotspot.skinid=='box_ui_location_kategori') {
				hotspot.skinid = 'box_ui_location_kategori';
				hsinst = new SkinHotspotClass_box_ui_location_kategori(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		} else
			if (hotspot.skinid=='360_video_experience_project') {
				hotspot.skinid = '360_video_experience_project';
				hsinst = new SkinHotspotClass__360_video_experience_project(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		} else
		{
				hotspot.skinid = 'hotspot-360-out';
				hsinst = new SkinHotspotClass_hotspot360out(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		}
		return hsinst;
	}
	me.removeSkinHotspots=function() {
		hotspotTemplates = {};
	}
	player.addListener('hotspotsremoved',function() {
			me.removeSkinHotspots();
	});
	player.addListener('changenode', function() {
		me.ggUserdata=player.userdata;
	});
	me.skinTimerEvent=function() {
		if (player.isInVR()) return;
		me.ggCurrentTime=new Date().getTime();
		for (const id in hotspotTemplates) {
			const tmpl=hotspotTemplates[id];
			tmpl.forEach(function(hotspot) {
				if (hotspot.hotspotTimerEvent) {
					hotspot.hotspotTimerEvent();
				}
			});
		};
	};
	player.addListener('timer', me.skinTimerEvent);
	me.addSkin();
	var style = document.createElement('style');
	style.type = 'text/css';
	style.appendChild(document.createTextNode('.ggskin { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; line-height: normal; } .ggmarkdown p,.ggmarkdown h1,.ggmarkdown h2,.ggmarkdown h3,.ggmarkdown h4 { margin-top: 0px } .ggmarkdown { white-space:normal }'));
	document.head.appendChild(style);
	document.addEventListener('keyup', function(e) {
		if (e.key === 'Enter' || e.key === ' ') {
			let activeElement = document.activeElement;
			if (activeElement.classList.contains('ggskin') && activeElement.onclick) activeElement.onclick();
		}
	});
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Enter' || e.key === ' ') {
			let activeElement = document.activeElement;
			if (activeElement.classList.contains('ggskin') && activeElement.onmousedown) activeElement.onmousedown();
		}
	});
	document.addEventListener('keyup', function(e) {
		if (e.key === 'Enter' || e.key === ' ') {
			let activeElement = document.activeElement;
			if (activeElement.classList.contains('ggskin') && activeElement.onmouseup) activeElement.onmouseup();
		}
	});
	me.skinTimerEvent();
	document.fonts.onloadingdone = () => {
		me.updateSize(me.divSkin);
	}
	player.addTranslations({"languages":["ar","en-US","zh","zh-CN"],"projectLanguage":"id-ID","translations":{"1":["","","",""],"<iframe src=\"../webview/discover.php?disc=%1&lang=id\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>":["<iframe src=\"../webview/discover.php?disc=%1&lang=sa\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","<iframe src=\"../webview/discover.php?disc=%1&lang=en\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","","<iframe src=\"../webview/discover.php?disc=%1&lang=zh\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>"],"<iframe src=\"../webview/informasi.php?info=%1&lang=id\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>":["<iframe src=\"../webview/informasi.php?info=%1&lang=sa\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","<iframe src=\"../webview/informasi.php?info=%1&lang=en\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","","<iframe src=\"../webview/informasi.php?info=%1&lang=zh\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>"],"<iframe src=\"../webview/kategori.php?lang=id\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>":["<iframe src=\"../webview/kategori.php?lang=sa\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","<iframe src=\"../webview/kategori.php?lang=en\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","","<iframe src=\"../webview/kategori.php?lang=zh\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>"],"<iframe src=\"../webview/menu.php?menu=%1&lang=id\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>":["<iframe src=\"../webview/menu.php?menu=%1&lang=sa\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","<iframe src=\"../webview/menu.php?menu=%1&lang=en\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","","<iframe src=\"../webview/menu.php?menu=%1&lang=zh\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>"],"<iframe src=\"../webview/publicservice.php?&lang=id\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>":["<iframe src=\"../webview/publicservice.php?&lang=sa\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","<iframe src=\"../webview/publicservice.php?&lang=en\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>","","<iframe src=\"../webview/publicservice.php?&lang=zh\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>"],"<iframe src=\"https://www.chatbase.co/chatbot-iframe/_aTmi_1uRjTFQ039x2uua\n\"\nwidth=\"100%\";\nstyle=\"height: 100%; min-height: 100%; border-radius:10px;\"\nframeborder=\"0\"></iframe>":["","","",""],"Enable Audio?":["","","",""],"Informasi":["معلومات","Information","","信息"],"Informasi\n":["معلومات\n","Information\n","","信息\n"],"Jakarta":["جاكرتا","","","雅加达"],"Jelajah":["استكشاف","Discover","","探索"],"Kategori":["الفئة","Category","","类别"],"Layanan Publik":["الخدمات العامة","Public Service","","公共服务"],"MENU":["القائمة","","","菜单"],"MORE":["المزيد","","","更多"],"Menu":["قائمة","","","菜单"],"Monumen Nasional":["النصب التذكاري الوطني","National Monument","","国家纪念碑"],"NO":["لا","","","否"],"Pantai Tirang":["","","",""],"Selamat datang di Monumen Nasional (Monas), kebanggaan Jakarta dan simbol perjuangan bangsa Indonesia. Jelajahi area bersejarahnya, nikmati keindahan taman yang rindang, dan kunjungi museum di bagian dasar monumen. Dari puncak obelisk berlapis emas, saksikan panorama menakjubkan ibu kota yang memancarkan semangat kemerdekaan.":["","","",""],"YES":["","","",""],"_custom":["","","",""],"start":["","","",""]}})
	if (!player.languageSet) player.setLanguage(window.navigator.language);
};
