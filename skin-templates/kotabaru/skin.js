// Garden Gnome Software - Skin
// Pano2VR 7.1.12/21036
// Filename: 
// Generated 2026-11-20T14:46:35

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
	player.addVariable('VB', 0, "start", { ignoreInState: 0  });
	player.addVariable('chatbot', 2, false, { ignoreInState: 0  });
	player.addVariable('welcometext', 0, "Selamat datang di Virtual Tour Monitoring Konstruksi Pelabuhan Perikanan Nusantara (PPN) Pengambengan Jembrana. Jelajahi kemajuan pembangunan infrastruktur perikanan strategis ini secara interaktif dan real-time langsung dari perangkat Anda untuk memantau transformasi kawasan pelabuhan secara menyeluruh.", { ignoreInState: 0  });
	player.addVariable('link', 0, "pengambengan", { ignoreInState: 0  });
	player.addVariable('video_is_ended', 2, false, { ignoreInState: 0  });
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
		el=me._shadow_up=document.createElement('div');
		el.ggId="Shadow Up";
		el.ggDx=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_rectangle shadow-up";
		el.ggType='rectangle';
		hs ='';
		hs+='border : 0px solid #000000;';
		hs+='height : 15%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : 0%;';
		hs+='visibility : hidden;';
		hs+='width : 100%;';
		hs+='pointer-events:none;';
		hs+='background: linear-gradient(to bottom, rgba(0,0,0,0.4), rgba(0,0,0,0));';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._shadow_up.ggIsActive=function() {
			return false;
		}
		el.ggElementNodeId=function() {
			return player.getCurrentNode();
		}
		me._shadow_up.ggUpdatePosition=function (useTransition) {
		}
		me.divSkin.appendChild(me._shadow_up);
		el=me._shadow_bottom=document.createElement('div');
		el.ggId="Shadow Bottom";
		el.ggDx=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_rectangle shadow-bottom";
		el.ggType='rectangle';
		hs ='';
		hs+='border : 0px solid #000000;';
		hs+='bottom : 0%;';
		hs+='height : 15%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='visibility : hidden;';
		hs+='width : 100%;';
		hs+='pointer-events:none;';
		hs+='background: linear-gradient(to bottom, rgba(0,0,0,0), rgba(0,0,0,0.8));';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._shadow_bottom.ggIsActive=function() {
			return false;
		}
		el.ggElementNodeId=function() {
			return player.getCurrentNode();
		}
		me._shadow_bottom.ggUpdatePosition=function (useTransition) {
		}
		me.divSkin.appendChild(me._shadow_bottom);
		el=me._hotspot=document.createElement('div');
		el.ggId="hotspot";
		el.ggDx=-0.02;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_container ";
		el.ggType='container';
		hs ='';
		hs+='height : 100%;';
		hs+='left : calc(50% - ((100% + 0px) / 2) - 0.02%);';
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
			if (hotspotTemplates.hasOwnProperty('hotspot_label')) {
				for(var i = 0; i < hotspotTemplates['hotspot_label'].length; i++) {
					hotspotTemplates['hotspot_label'][i].ggEvent_activehotspotchanged();
				}
			}
			if (hotspotTemplates.hasOwnProperty('360_video_experience_project_point')) {
				for(var i = 0; i < hotspotTemplates['360_video_experience_project_point'].length; i++) {
					hotspotTemplates['360_video_experience_project_point'][i].ggEvent_activehotspotchanged();
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
			if (hotspotTemplates.hasOwnProperty('hotspot_label')) {
				for(var i = 0; i < hotspotTemplates['hotspot_label'].length; i++) {
					hotspotTemplates['hotspot_label'][i].ggEvent_changenode();
				}
			}
			if (hotspotTemplates.hasOwnProperty('360_video_experience_project_point')) {
				for(var i = 0; i < hotspotTemplates['360_video_experience_project_point'].length; i++) {
					hotspotTemplates['360_video_experience_project_point'][i].ggEvent_changenode();
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
			if (hotspotTemplates.hasOwnProperty('hotspot_label')) {
				for(var i = 0; i < hotspotTemplates['hotspot_label'].length; i++) {
					hotspotTemplates['hotspot_label'][i].ggEvent_configloaded();
				}
			}
			if (hotspotTemplates.hasOwnProperty('360_video_experience_project_point')) {
				for(var i = 0; i < hotspotTemplates['360_video_experience_project_point'].length; i++) {
					hotspotTemplates['360_video_experience_project_point'][i].ggEvent_configloaded();
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
			if (hotspotTemplates.hasOwnProperty('box_ui_location_project')) {
				for(var i = 0; i < hotspotTemplates['box_ui_location_project'].length; i++) {
					hotspotTemplates['box_ui_location_project'][i].ggEvent_varchanged_vis_skin();
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
	function SkinHotspotClass__360_video_experience_project_point(parentScope,hotspot) {
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
		el=me.__360_video_experience_project_point=document.createElement('div');
		el.ggId="360_video_experience_project_point";
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
		me.__360_video_experience_project_point.ggIsActive=function() {
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
		me.__360_video_experience_project_point.onclick=function (e) {
			player.videoPanoPlay();
			var list=me.findElements("hotspot_darat_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			var list=me.findElements("drone_icon_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			var list=me.findElements("box_ui_location_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			var list=me.findElements("360_video_experience_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			var list=me.findElements("hotspot-360-out",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility=(Number(e.style.opacity)>0||!e.style.opacity)?'inherit':'hidden';
				e.ggVisible=true;
			}
			var list=me.findElements("box_ui_location_kategori",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			var list=me.findElements("hotspot_label",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			player.changePolygonMode(-1);
			(function(){ var els = document.querySelectorAll('*'); els.forEach(function(el) { if(el.style.width === '300px' && el.style.height === '300px') { el.style.display = 'none'; } }); })();
			player.triggerEvent('hsproxyclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me.__360_video_experience_project_point.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me.__360_video_experience_project_point.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['_360_video_experience_project_point']=true;
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me.__360_video_experience_project_point.onmouseleave=function (e) {
			me.elementMouseOver['_360_video_experience_project_point']=false;
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me.__360_video_experience_project_point.ggUpdatePosition=function (useTransition) {
		}
		el=me._txthotspot360=document.createElement('div');
		els=me._txthotspot360__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot-360";
		el.ggDx=0;
		el.ggDy=-80;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text ";
		el.ggType='text';
		hs ='';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 5px;';
		hs+='color : rgba(255,255,255,1);';
		hs+='height : 60px;';
		hs+='left : calc(50% - ((230px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((60px + 0px) / 2) - 80px);';
		hs+='visibility : inherit;';
		hs+='width : 230px;';
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
		hs+='font-size: 18px;';
		hs+='font-weight: inherit;';
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
		me.__360_video_experience_project_point.appendChild(me._txthotspot360);
		el=me._point=document.createElement('div');
		els=me._point__img=document.createElement('img');
		els.className='ggskin ggskin_point';
		hs='data:image/webp;base64,UklGRjILAABXRUJQVlA4WAoAAAAwAAAAEgQAEgQASUNDUMsBAAAAAAHLAAAAAAJAAABtbnRyUkdCIFhZWiAAAAAAAAAAAAAAAABhY3NwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAA9tYAAQAAAADTLVF0BgIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAlyWFlaAAAA8AAAABRnWFlaAAABBAAAABRiWFlaAAABGAAAABR3dHB0AAABLAAAABRjcHJ0AAABQAAAAAxyVFJDAAABTAAAACBnVFJDAAABTAAAACBiVFJDAAABTAAAACBkZXNjAAABbAAAAF9YWVogAAAAAAAAb58AADj0AAADkVhZWiAAAAAAAABilgAAt4cAABjcWFlaIAAAAAAAACShAAAPhQAAtt'+
			'NYWVogAAAAAAAA808AAQAAAAEWwnRleHQAAAAATi9BAHBhcmEAAAAAAAMAAAACZmYAAPKnAAANWQAAE9AAAApbZGVzYwAAAAAAAAAFc1JHQgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQUxQSPQAAAARJ0CQbTOEqczw5teIiOA5KGgkqaEZIDgi+Ff1CmgT0f8JSPzP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zP//zvqqOHmf/4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4'+
			'j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4j//4zzDmCtsOcCICVlA4IEQIAABw+ACdASoTBBMEPjEYi0QiIaEQZAAgAwS0t3C7sI2gGN7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMo'+
			'B7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB7qb6NpYad'+
			'UGghlUAjfRtQ5xlAPdTfRtQ5xlAPdTfRtQ5xlAPdTbnTwyoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB57p4ZUA91N9G1DnGUA91N9G1DnGUA91N9G1DnGUA8908MqAe6m+jahzjKAe6m+jahzjKAe6m+jahzjKAee6eGVAPdTfRtQ5xlAPdTfRtQ5xlAPdTfRtQ5xlAPPdPDKgHupvo2oc4ygHupvo2oc4ygHupvo2oc4ygHnunhlQD3U30bUOcZQD3U30bUOcZQD3U30bUOcZQDz3TwyoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB57p4ZUA91N9G1DnGUA91N9G1DnGUA91N9G1DnGUA8908MqAe6m+jahzjKAe6m+jahzjKAe6m+jahzjKAee6eGVAPdTfRtQ5xlAPdTfRtQ5xlAPdTfRtQ5xlAPPdPDKgH'+
			'upvo2oc4ygHupvo2oc4ygHupvo2oc4ygHnunhlQD3U30bUOcZQD3U30bUOcZQD3U30bUOcZQDz3TwyoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB57p4ZUA91N9G1DnGUA91N9G1DnGUA91N9G1DnGUA8908MqAe6m+jahzjKAe6m+jahzjKAe6m+jahzjKAee6eGVAPdTfRtQ5xlAPdTfRtQ5xlAPdTfRtQ5xlAPPdPDKgHupvo2oc4ygHupvo2oc4ygHupvo2oc4ygHnunhlQD3U30bUOcZQD3U30bUOcZQD3U30bUOcZQDz3TwyoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB57p4ZUA91N9G1DnGUA91N9G1DnGUA91N9G1DnGUA8908MqAe6m+jahzjKAe6m+jahzjKAe6m+jahzjKAee6eGVAPdTfRtQ5x'+
			'lAPdTfRtQ5xlAPdTfRtQ5xlAPPdPDKgHupvo2oc4ygHupvo2oc4ygHupvo2oc4ygHnunhlQD3U30bUOcZQD3U30bUOcZQD3U30bUOcZQDz3TwyoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB57p4ZUA91N9G1DnGUA91N9G1DnGUA91N9G1DnGUA8908MqAe6m+jahzjKAe6m+jahzjKAe6m+jahzjKAee6eGVAPdTfRtQ5xlAPdTfRtQ5xlAPdTfRtQ5xlAPPdPDKgHupvo2oc4ygHupvo2oc4ygHupvo2oc4ygHnunhlQD3U30bUOcZQD3U30bUOcZQD3U30bUOcZQDz3TwyoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB57p4ZUA91N9G1DnGUA91N9G1DnGUA91N9G1DnGUA8908MqAe6m+jahzjKAe6m+ja'+
			'hzjKAe6m+jahzjKAee6eGVAPdTfRtQ5xlAPdTfRtQ5xlAPdTfRtQ5xlAPPdPDKgHupvo2oc4ygHupvo2oc4ygHupvo2oc4ygHnunhlQD3U30bUOcZQD3U30bUOcZQD3U30bUOcZQDz3TwyoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB57p4ZUA91N9G1DnGUA91N9G1DnGUA91N9G1DnGUA8908MqAe6m+jahzjKAe6m+jahzjKAe6m+jahzjKAee6eGVAPdTfRtQ5xlAPdTfRtQ5xlAPdTfRtQ5xlAPPdPDKgHupvo2oc4ygHupvo2oc4ygHupvo2oc4ygHnunhlQD3U30bUOcZQD3U30bUOcZQD3U30bUOcZQDz3TwyoB7qb6NqHOMoB7qb6NqHOMoB7qb6NqHOMoB57p4ZUA91N9G1DnGUA91N9G1DnGUA91N'+
			'9G1DnGUA8908MqAe6m+jahzjKAe6m+jahzjKAe6m+jahzjKAee6eGVAPdTfRtQ5xlAPdTfPwAP7/+C2QAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJX//5JwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
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
		el.ggId="point";
		el.ggDx=0;
		el.ggDy=65;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_image ";
		el.ggType='image';
		hs ='';
		hs+='height : 100px;';
		hs+='left : calc(50% - ((150px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((100px + 0px) / 2) + 65px);';
		hs+='visibility : inherit;';
		hs+='width : 150px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._point.ggIsActive=function() {
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
		me._point.ggUpdatePosition=function (useTransition) {
		}
		me.__360_video_experience_project_point.appendChild(me._point);
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
		me._ht_node_custom_image.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_custom_image'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_custom_image.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_custom_image.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_custom_image.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image.ggCurrentLogicStateScaling == 0) {
					me._ht_node_custom_image.ggParameter.sx = 1.04;
					me._ht_node_custom_image.ggParameter.sy = 1.04;
					me._ht_node_custom_image.style.transform=parameterToTransform(me._ht_node_custom_image.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image);}, 250);
				}
				else {
					me._ht_node_custom_image.ggParameter.sx = 1;
					me._ht_node_custom_image.ggParameter.sy = 1;
					me._ht_node_custom_image.style.transform=parameterToTransform(me._ht_node_custom_image.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image);}, 250);
				}
			}
		}
		me._ht_node_custom_image.logicBlock_scaling();
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
				me._ht_node_custom_image.style.transition='transform 200ms ease 0ms';
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
		me._ht_node_custom_image.onclick=function (e) {
			if (player.transitionsDisabled) {
				me._deschotspot360.style.transition='none';
			} else {
				me._deschotspot360.style.transition='all 500ms ease-out 0ms';
			}
			me._deschotspot360.style.opacity='0';
			me._deschotspot360.style.visibility='hidden';
		}
		me._ht_node_custom_image.onmouseenter=function (e) {
			if (player.transitionsDisabled) {
				me._deschotspot360.style.transition='none';
			} else {
				me._deschotspot360.style.transition='all 500ms ease-out 0ms';
			}
			me._deschotspot360.style.opacity='1';
			me._deschotspot360.style.visibility=me._deschotspot360.ggVisible?'inherit':'hidden';
			var list=me.findElements("hotspot_label",true);
			while(list.length>0) {
				var e=list.pop();
				if (player.transitionsDisabled) {
					e.style.transition='none';
				} else {
					e.style.transition='all 500ms ease-out 0ms';
				}
				e.style.opacity='0';
				e.style.visibility='hidden';
			}
			var list=me.findElements("drone_icon_project",true);
			while(list.length>0) {
				var e=list.pop();
				if (player.transitionsDisabled) {
					e.style.transition='none';
				} else {
					e.style.transition='all 500ms ease-out 0ms';
				}
				e.style.opacity='0';
				e.style.visibility='hidden';
			}
			var list=me.findElements("hotspot_darat_project",true);
			while(list.length>0) {
				var e=list.pop();
				if (player.transitionsDisabled) {
					e.style.transition='none';
				} else {
					e.style.transition='all 500ms ease-out 0ms';
				}
				e.style.opacity='0';
				e.style.visibility='hidden';
			}
			me.elementMouseOver['ht_node_custom_image']=true;
			me._ht_node_custom_image.logicBlock_scaling();
		}
		me._ht_node_custom_image.onmouseleave=function (e) {
			if (player.transitionsDisabled) {
				me._deschotspot360.style.transition='none';
			} else {
				me._deschotspot360.style.transition='all 500ms ease-out 0ms';
			}
			me._deschotspot360.style.opacity='0';
			me._deschotspot360.style.visibility='hidden';
			var list=me.findElements("hotspot_label",true);
			while(list.length>0) {
				var e=list.pop();
				if (player.transitionsDisabled) {
					e.style.transition='none';
				} else {
					e.style.transition='all 500ms ease-out 0ms';
				}
				e.style.opacity='1';
				e.style.visibility=e.ggVisible?'inherit':'hidden';
			}
			var list=me.findElements("drone_icon_project",true);
			while(list.length>0) {
				var e=list.pop();
				if (player.transitionsDisabled) {
					e.style.transition='none';
				} else {
					e.style.transition='all 500ms ease-out 0ms';
				}
				e.style.opacity='1';
				e.style.visibility=e.ggVisible?'inherit':'hidden';
			}
			var list=me.findElements("hotspot_darat_project",true);
			while(list.length>0) {
				var e=list.pop();
				if (player.transitionsDisabled) {
					e.style.transition='none';
				} else {
					e.style.transition='all 500ms ease-out 0ms';
				}
				e.style.opacity='1';
				e.style.visibility=e.ggVisible?'inherit':'hidden';
			}
			me.elementMouseOver['ht_node_custom_image']=false;
			me._ht_node_custom_image.logicBlock_scaling();
		}
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
		me.__360_video_experience_project_point.appendChild(me._ht_node_custom_image);
		el=me._deschotspot360=document.createElement('div');
		els=me._deschotspot360__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="desc-hotspot-360";
		el.ggDy=35;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'translate(0px, -50%) ' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text ";
		el.ggType='text';
		hs ='';
		hs+='color : rgba(255,255,255,1);';
		hs+='height : auto;';
		hs+='left : 110px;';
		hs+='opacity : 0;';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 35px);';
		hs+='transform : translate(0px, -50%);;';
		hs+='visibility : hidden;';
		hs+='width : 160px;';
		hs+='pointer-events:none;';
		hs+='\/* Background & Glassmorphism *\/ backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); \/* Box Model, Border & Shadow *\/ margin: 10px 18px; padding: 20px 28px; border: 1px solid rgba(120, 220, 255, 0.25); border-top: 1px solid rgba(180, 240, 255, 0.5); border-left: 4px solid #ffffff; \/* Efek Tooltip: Sudut Kiri Atas Lancip (0), sisanya membulat (24px) *\/ border-radius: 0 24px 24px 24px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15), inset 0 1px 1px rgba(255, 255, 255, 0.6), 0 0 25px rgba(255, 255, 255, 0.4); \/* Typography *\/ color: #ffffff; font-family: \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; font-size: 16px; letter-spacing: 0.5px; line-height: 1.6; text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		hs ='';
		hs += 'box-sizing: border-box;';
		hs+='width: 100%;';
		hs+='height: auto;';
		hs+='pointer-events: none;';
		hs+='border : 0px solid #ffffff;';
		hs+='border-radius : 5px;';
		hs+='font-size: 16px;';
		hs+='font-weight: inherit;';
		hs+='text-align: left;';
		hs+='white-space: pre-line;';
		hs+='padding: 1px 1px 1px 1px;';
		hs+='overflow: hidden;';
		els.setAttribute('style',hs);
		me._deschotspot360.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.description)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._deschotspot360.ggUpdateText();
		player.addListener('changenode', function() {
			me._deschotspot360.ggUpdateText();
		});
		el.appendChild(els);
		me._deschotspot360.ggIsActive=function() {
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
		me._deschotspot360.ggUpdatePosition=function (useTransition) {
		}
		me.__360_video_experience_project_point.appendChild(me._deschotspot360);
		me.elementMouseOver['_360_video_experience_project_point']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image.logicBlock_scaling();
		me._ht_node_custom_image.logicBlock_visible();
		me.elementMouseOver['ht_node_custom_image']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image.logicBlock_visible();
			};
			me.__div = me.__360_video_experience_project_point;
	};
	function SkinHotspotClass_hotspot_label(parentScope,hotspot) {
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
		el=me._hotspot_label=document.createElement('div');
		el.ggId="hotspot_label";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_hotspot hotspot";
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
		me._hotspot_label.ggIsActive=function() {
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
		me._hotspot_label.onclick=function (e) {
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
		me._hotspot_label.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._hotspot_label.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['hotspot_label']=true;
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._hotspot_label.onmouseleave=function (e) {
			me.elementMouseOver['hotspot_label']=false;
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me._hotspot_label.ggUpdatePosition=function (useTransition) {
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
				me._ht_node_custom_image0.style.transition='';
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
		me._hotspot_label.appendChild(me._ht_node_custom_image0);
		el=me._txthotspot=document.createElement('div');
		els=me._txthotspot__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot";
		el.ggDx=0;
		el.ggDy=-35;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=true;
		el.className="ggskin ggskin_text hotspot-label";
		el.ggType='text';
		hs ='';
		hs+='border-color : #ffffff;';
		hs+='border-style : solid;';
		hs+='border-width : 0px 0px 0px 0px;';
		hs+='color : rgba(255,255,255,1);';
		hs+='cursor : pointer;';
		hs+='height : 30px;';
		hs+='left : calc(50% - ((150px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((30px + 0px) / 2) - 35px);';
		hs+='visibility : inherit;';
		hs+='width : 150px;';
		hs+='pointer-events:none;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		hs ='';
		hs += 'box-sizing: border-box;';
		hs+='width: 100%;';
		hs+='height: auto;';
		hs+='max-height: 100%;';
		hs+='pointer-events: none;';
		hs+='font-size: 12px;';
		hs+='font-weight: inherit;';
		hs+='text-align: center;';
		hs+='position: absolute;';
		hs+='top: 50%;';
		hs+='transform: translate(0, -50%);';
		hs+='white-space: pre-line;';
		hs+='padding: 0px 0px 0px 0px;';
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
		me._hotspot_label.appendChild(me._txthotspot);
		me.elementMouseOver['hotspot_label']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image0.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image0.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image0.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image0.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image0.logicBlock_visible();
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image0.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image0.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image0.logicBlock_visible();
			};
			me.__div = me._hotspot_label;
	};
	function SkinHotspotClass_nama_lokasi(parentScope,hotspot) {
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
		el=me._nama_lokasi=document.createElement('div');
		el.ggId="nama_lokasi";
		el.ggDx=0;
		el.ggDy=0;
		el.ggParameter={ rx:0,ry:0,a:0,sx:1,sy:1,def:'' };
		el.ggVisible=false;
		el.className="ggskin ggskin_hotspot ";
		el.ggType='hotspot';
		hs ='';
		hs+='height : 0px;';
		hs+='left : calc(50% - ((0px + 0px) / 2) + 0px);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 0px);';
		hs+='visibility : hidden;';
		hs+='width : 0px;';
		hs+='pointer-events:auto;';
		el.setAttribute('style',hs);
		el.style.transformOrigin='50% 50%';
		me._nama_lokasi.ggIsActive=function() {
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
		me._nama_lokasi.onclick=function (e) {
			player.triggerEvent('hsproxyclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._nama_lokasi.ondblclick=function (e) {
			player.triggerEvent('hsproxydblclick', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._nama_lokasi.onmouseenter=function (e) {
			player.setActiveHotspot(me.hotspot);
			me.elementMouseOver['nama_lokasi']=true;
			player.triggerEvent('hsproxyover', {'id': me.hotspot.id, 'url': me.hotspot.url});
		}
		me._nama_lokasi.onmouseleave=function (e) {
			me.elementMouseOver['nama_lokasi']=false;
			player.triggerEvent('hsproxyout', {'id': me.hotspot.id, 'url': me.hotspot.url});
			player.setActiveHotspot(null);
		}
		me._nama_lokasi.ggUpdatePosition=function (useTransition) {
		}
		me.elementMouseOver['nama_lokasi']=false;
			me.__div = me._nama_lokasi;
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
		el.ggVisible=false;
		el.className="ggskin ggskin_hotspot ";
		el.ggType='hotspot';
		hs ='';
		hs+='z-index: -1;';
		hs+='cursor : pointer;';
		hs+='height : 0px;';
		hs+='left : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='position : absolute;';
		hs+='top : calc(50% - ((0px + 0px) / 2) + 0%);';
		hs+='visibility : hidden;';
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
			var list=me.findElements("box_ui_location_kategori",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility=(Number(e.style.opacity)>0||!e.style.opacity)?'inherit':'hidden';
				e.ggVisible=true;
			}
			var list=me.findElements("hotspot_darat_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility=(Number(e.style.opacity)>0||!e.style.opacity)?'inherit':'hidden';
				e.ggVisible=true;
			}
			var list=me.findElements("360_video_experience_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility=(Number(e.style.opacity)>0||!e.style.opacity)?'inherit':'hidden';
				e.ggVisible=true;
			}
			var list=me.findElements("drone_icon_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility=(Number(e.style.opacity)>0||!e.style.opacity)?'inherit':'hidden';
				e.ggVisible=true;
			}
			var list=me.findElements("hotspot_label",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility=(Number(e.style.opacity)>0||!e.style.opacity)?'inherit':'hidden';
				e.ggVisible=true;
			}
			player.changePolygonMode(2);
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
				me._ht_node_custom_image1.style.transition='';
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
		me._hotspot360out.appendChild(me._ht_node_custom_image1);
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
		el=me._txthotspot0=document.createElement('div');
		els=me._txthotspot0__text=document.createElement('div');
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
		me._hotspot360out.appendChild(me._txthotspot0);
		me.elementMouseOver['hotspot360out']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image1.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image1.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image1.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image1.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image1.logicBlock_visible();
		me._rectangle_1.logicBlock_scaling();
		me.elementMouseOver['rectangle_1']=false;
		me._ht_node_thumb_1.logicBlock_scaling();
		me.elementMouseOver['ht_node_thumb_1']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image1.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image1.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image1.logicBlock_visible();
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
			player.videoPanoPlay();
			var list=me.findElements("hotspot_darat_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			var list=me.findElements("drone_icon_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			var list=me.findElements("box_ui_location_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			var list=me.findElements("360_video_experience_project",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
			var list=me.findElements("hotspot-360-out",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility=(Number(e.style.opacity)>0||!e.style.opacity)?'inherit':'hidden';
				e.ggVisible=true;
			}
			var list=me.findElements("box_ui_location_kategori",true);
			while(list.length>0) {
				var e=list.pop();
				e.style.transition='none';
				e.style.visibility='hidden';
				e.ggVisible=false;
			}
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
		me.__360_video_experience_project.appendChild(me._ht_node_custom_image2);
		el=me._txthotspot3600=document.createElement('div');
		els=me._txthotspot3600__text=document.createElement('div');
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
		me._txthotspot3600.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.title)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._txthotspot3600.ggUpdateText();
		player.addListener('changenode', function() {
			me._txthotspot3600.ggUpdateText();
		});
		el.appendChild(els);
		me._txthotspot3600.ggIsActive=function() {
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
		me._txthotspot3600.ggUpdatePosition=function (useTransition) {
		}
		me.__360_video_experience_project.appendChild(me._txthotspot3600);
		me.elementMouseOver['_360_video_experience_project']=false;
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
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image2.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image2.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image2.logicBlock_visible();
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
		me._box_ui_location_kategori.appendChild(me._ht_node_custom_image3);
		el=me._txthotspot1=document.createElement('div');
		els=me._txthotspot1__text=document.createElement('div');
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
		me._box_ui_location_kategori.appendChild(me._txthotspot1);
		me.elementMouseOver['box_ui_location_kategori']=false;
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
				me._ht_node_custom_image3.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image3.logicBlock_visible();
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
		el.className="ggskin ggskin_hotspot hotspot";
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
		me._drone_icon_project.appendChild(me._ht_node_custom_image4);
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
		el=me._txthotspot2=document.createElement('div');
		els=me._txthotspot2__text=document.createElement('div');
		el.className='ggskin ggskin_textdiv';
		el.ggTextDiv=els;
		el.ggId="txt-hotspot";
		el.ggDx=0;
		el.ggDy=-56;
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
		hs+='top : calc(50% - ((60px + 0px) / 2) - 56px);';
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
		hs+='font-size: 12px;';
		hs+='font-weight: inherit;';
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
		me._drone_icon_project.appendChild(me._txthotspot2);
		me.elementMouseOver['drone_icon_project']=false;
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
		me._rectangle_1_1.logicBlock_scaling();
		me._rectangle_1_1.logicBlock_alpha();
		me.elementMouseOver['rectangle_1_1']=false;
		me._ht_node_thumb_10.logicBlock_scaling();
		me.elementMouseOver['ht_node_thumb_10']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image4.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image4.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image4.logicBlock_visible();
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
		me._ht_node_custom_image5.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_custom_image5'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_custom_image5.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_custom_image5.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_custom_image5.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image5.ggCurrentLogicStateScaling == 0) {
					me._ht_node_custom_image5.ggParameter.sx = 1.04;
					me._ht_node_custom_image5.ggParameter.sy = 1.04;
					me._ht_node_custom_image5.style.transform=parameterToTransform(me._ht_node_custom_image5.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image5);}, 250);
				}
				else {
					me._ht_node_custom_image5.ggParameter.sx = 1;
					me._ht_node_custom_image5.ggParameter.sy = 1;
					me._ht_node_custom_image5.style.transform=parameterToTransform(me._ht_node_custom_image5.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image5);}, 250);
				}
			}
		}
		me._ht_node_custom_image5.logicBlock_scaling();
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
				me._ht_node_custom_image5.style.transition='transform 200ms ease 0ms';
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
		me._ht_node_custom_image5.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_custom_image5']=true;
			me._ht_node_custom_image5.logicBlock_scaling();
		}
		me._ht_node_custom_image5.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_custom_image5']=false;
			me._ht_node_custom_image5.logicBlock_scaling();
		}
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
		me._box_ui_location_project.appendChild(me._ht_node_custom_image5);
		el=me._txthotspot3=document.createElement('div');
		els=me._txthotspot3__text=document.createElement('div');
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
		me._box_ui_location_project.appendChild(me._txthotspot3);
		me._box_ui_location_project.logicBlock_visible();
		me.elementMouseOver['box_ui_location_project']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image5.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image5.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image5.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image5.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image5.logicBlock_scaling();
		me._ht_node_custom_image5.logicBlock_visible();
		me.elementMouseOver['ht_node_custom_image5']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image5.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._box_ui_location_project.logicBlock_visible();
				me._ht_node_custom_image5.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._box_ui_location_project.logicBlock_visible();
				me._ht_node_custom_image5.logicBlock_visible();
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
		el=me._ht_node_custom_image6=document.createElement('div');
		els=me._ht_node_custom_image6__img=document.createElement('img');
		els.className='ggskin ggskin_external';
		hs ='';
		hs += 'position: absolute;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.onload=function() {me._ht_node_custom_image6.ggUpdatePosition();}
		el.appendChild(els);
		el.ggSubElement = els;
		hs ='';
		el.ggAltText="";
		el.ggScrollbars=false;
		el.ggUpdateText = function() {
			me._ht_node_custom_image6.ggSubElement.setAttribute('alt', player._(me._ht_node_custom_image6.ggAltText));
			me._ht_node_custom_image6.ggUpdateImageTranslation();
		}
		el.ggSetImage = function(img) {
			me._ht_node_custom_image6.ggText_untranslated = img;
			me._ht_node_custom_image6.ggUpdateImageTranslation();
		}
		el.ggUpdateImage = function() {
			me._ht_node_custom_image6.ggSubElement.style.width = '0px';
			me._ht_node_custom_image6.ggSubElement.style.height = '0px';
			me._ht_node_custom_image6.ggSubElement.src='';
			me._ht_node_custom_image6.ggSubElement.src=me._ht_node_custom_image6.ggText;
		}
		el.ggUpdateImageTranslation = function() {
			if (me._ht_node_custom_image6.ggText != player._(me._ht_node_custom_image6.ggText_untranslated)) {
				me._ht_node_custom_image6.ggText = player._(me._ht_node_custom_image6.ggText_untranslated);
				me._ht_node_custom_image6.ggUpdateImage()
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
		me._ht_node_custom_image6.ggIsActive=function() {
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
		me._ht_node_custom_image6.logicBlock_scaling = function() {
			var newLogicStateScaling;
			if (
				((me.elementMouseOver['ht_node_custom_image6'] == true))
			)
			{
				newLogicStateScaling = 0;
			}
			else {
				newLogicStateScaling = -1;
			}
			if (me._ht_node_custom_image6.ggCurrentLogicStateScaling != newLogicStateScaling) {
				me._ht_node_custom_image6.ggCurrentLogicStateScaling = newLogicStateScaling;
				me._ht_node_custom_image6.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image6.ggCurrentLogicStateScaling == 0) {
					me._ht_node_custom_image6.ggParameter.sx = 1.04;
					me._ht_node_custom_image6.ggParameter.sy = 1.04;
					me._ht_node_custom_image6.style.transform=parameterToTransform(me._ht_node_custom_image6.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image6);}, 250);
				}
				else {
					me._ht_node_custom_image6.ggParameter.sx = 1;
					me._ht_node_custom_image6.ggParameter.sy = 1;
					me._ht_node_custom_image6.style.transform=parameterToTransform(me._ht_node_custom_image6.ggParameter);
					setTimeout(function() {skin.updateSize(me._ht_node_custom_image6);}, 250);
				}
			}
		}
		me._ht_node_custom_image6.logicBlock_scaling();
		me._ht_node_custom_image6.logicBlock_visible = function() {
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
			if (me._ht_node_custom_image6.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._ht_node_custom_image6.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._ht_node_custom_image6.style.transition='transform 200ms ease 0ms';
				if (me._ht_node_custom_image6.ggCurrentLogicStateVisible == 0) {
					me._ht_node_custom_image6.style.visibility=(Number(me._ht_node_custom_image6.style.opacity)>0||!me._ht_node_custom_image6.style.opacity)?'inherit':'hidden';
					me._ht_node_custom_image6.ggSubElement.src=me._ht_node_custom_image6.ggText;
					me._ht_node_custom_image6.ggVisible=true;
				}
				else {
					me._ht_node_custom_image6.style.visibility="hidden";
					me._ht_node_custom_image6.ggSubElement.src='';
					me._ht_node_custom_image6.ggVisible=false;
				}
			}
		}
		me._ht_node_custom_image6.logicBlock_visible();
		me._ht_node_custom_image6.onmouseenter=function (e) {
			me.elementMouseOver['ht_node_custom_image6']=true;
			me._ht_node_custom_image6.logicBlock_scaling();
		}
		me._ht_node_custom_image6.onmouseleave=function (e) {
			me.elementMouseOver['ht_node_custom_image6']=false;
			me._ht_node_custom_image6.logicBlock_scaling();
		}
		me._ht_node_custom_image6.ggUpdatePosition=function (useTransition) {
			var parentWidth = me._ht_node_custom_image6.clientWidth;
			var parentHeight = me._ht_node_custom_image6.clientHeight;
			var img = me._ht_node_custom_image6__img;
			var aspectRatioDiv = me._ht_node_custom_image6.clientWidth / me._ht_node_custom_image6.clientHeight;
			var aspectRatioImg = img.naturalWidth / img.naturalHeight;
			var currentWidth,currentHeight;
			if ((hotspot) && (hotspot.customimage)) {
				currentWidth  = hotspot.customimagewidth;
				currentHeight = hotspot.customimageheight;
			}
			if (!me._ht_node_custom_image6.ggScrollbars || currentWidth < me._ht_node_custom_image6.clientWidth) {
				img.style.right='';
				img.style.left='50%';
				img.style.marginLeft='-' + currentWidth/2 + 'px';
			} else {
				img.style.right='';
				img.style.left='0px';
				img.style.marginLeft='0px';
				me._ht_node_custom_image6.scrollLeft=currentWidth / 2 - me._ht_node_custom_image6.clientWidth / 2;
			}
			if (!me._ht_node_custom_image6.ggScrollbars || currentHeight < me._ht_node_custom_image6.clientHeight) {
				img.style.bottom='';
				img.style.top='0px';
			} else {
				img.style.bottom='';
				img.style.top='0px';
				img.style.marginTop='0px';
			}
		}
		me._door_icon_project.appendChild(me._ht_node_custom_image6);
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
		el=me._txthotspot4=document.createElement('div');
		els=me._txthotspot4__text=document.createElement('div');
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
		me._door_icon_project.appendChild(me._txthotspot4);
		me.elementMouseOver['door_icon_project']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image6.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image6.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image6.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image6.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image6.logicBlock_scaling();
		me._ht_node_custom_image6.logicBlock_visible();
		me.elementMouseOver['ht_node_custom_image6']=false;
		me._rectangle_1_10.logicBlock_scaling();
		me._rectangle_1_10.logicBlock_alpha();
		me.elementMouseOver['rectangle_1_10']=false;
		me._ht_node_thumb_11.logicBlock_scaling();
		me.elementMouseOver['ht_node_thumb_11']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image6.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image6.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image6.logicBlock_visible();
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
		el.className="ggskin ggskin_hotspot hotspot";
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
		el=me._ht_node_custom_image7=document.createElement('div');
		els=me._ht_node_custom_image7__img=document.createElement('img');
		els.className='ggskin ggskin_external';
		hs ='';
		hs += 'position: absolute;-webkit-user-drag:none;pointer-events:none;;';
		els.setAttribute('style', hs);
		els.onload=function() {me._ht_node_custom_image7.ggUpdatePosition();}
		el.appendChild(els);
		el.ggSubElement = els;
		hs ='';
		el.ggAltText="";
		el.ggScrollbars=false;
		el.ggUpdateText = function() {
			me._ht_node_custom_image7.ggSubElement.setAttribute('alt', player._(me._ht_node_custom_image7.ggAltText));
			me._ht_node_custom_image7.ggUpdateImageTranslation();
		}
		el.ggSetImage = function(img) {
			me._ht_node_custom_image7.ggText_untranslated = img;
			me._ht_node_custom_image7.ggUpdateImageTranslation();
		}
		el.ggUpdateImage = function() {
			me._ht_node_custom_image7.ggSubElement.style.width = '0px';
			me._ht_node_custom_image7.ggSubElement.style.height = '0px';
			me._ht_node_custom_image7.ggSubElement.src='';
			me._ht_node_custom_image7.ggSubElement.src=me._ht_node_custom_image7.ggText;
		}
		el.ggUpdateImageTranslation = function() {
			if (me._ht_node_custom_image7.ggText != player._(me._ht_node_custom_image7.ggText_untranslated)) {
				me._ht_node_custom_image7.ggText = player._(me._ht_node_custom_image7.ggText_untranslated);
				me._ht_node_custom_image7.ggUpdateImage()
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
		me._ht_node_custom_image7.ggIsActive=function() {
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
		me._ht_node_custom_image7.logicBlock_visible = function() {
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
			if (me._ht_node_custom_image7.ggCurrentLogicStateVisible != newLogicStateVisible) {
				me._ht_node_custom_image7.ggCurrentLogicStateVisible = newLogicStateVisible;
				me._ht_node_custom_image7.style.transition='';
				if (me._ht_node_custom_image7.ggCurrentLogicStateVisible == 0) {
					me._ht_node_custom_image7.style.visibility=(Number(me._ht_node_custom_image7.style.opacity)>0||!me._ht_node_custom_image7.style.opacity)?'inherit':'hidden';
					me._ht_node_custom_image7.ggSubElement.src=me._ht_node_custom_image7.ggText;
					me._ht_node_custom_image7.ggVisible=true;
				}
				else {
					me._ht_node_custom_image7.style.visibility="hidden";
					me._ht_node_custom_image7.ggSubElement.src='';
					me._ht_node_custom_image7.ggVisible=false;
				}
			}
		}
		me._ht_node_custom_image7.logicBlock_visible();
		me._ht_node_custom_image7.ggUpdatePosition=function (useTransition) {
			var parentWidth = me._ht_node_custom_image7.clientWidth;
			var parentHeight = me._ht_node_custom_image7.clientHeight;
			var img = me._ht_node_custom_image7__img;
			var aspectRatioDiv = me._ht_node_custom_image7.clientWidth / me._ht_node_custom_image7.clientHeight;
			var aspectRatioImg = img.naturalWidth / img.naturalHeight;
			var currentWidth,currentHeight;
			if ((hotspot) && (hotspot.customimage)) {
				currentWidth  = hotspot.customimagewidth;
				currentHeight = hotspot.customimageheight;
			}
			if (!me._ht_node_custom_image7.ggScrollbars || currentWidth < me._ht_node_custom_image7.clientWidth) {
				img.style.right='';
				img.style.left='50%';
				img.style.marginLeft='-' + currentWidth/2 + 'px';
			} else {
				img.style.right='';
				img.style.left='0px';
				img.style.marginLeft='0px';
				me._ht_node_custom_image7.scrollLeft=currentWidth / 2 - me._ht_node_custom_image7.clientWidth / 2;
			}
			if (!me._ht_node_custom_image7.ggScrollbars || currentHeight < me._ht_node_custom_image7.clientHeight) {
				img.style.bottom='';
				img.style.top='0px';
			} else {
				img.style.bottom='';
				img.style.top='0px';
				img.style.marginTop='0px';
			}
		}
		me._hotspot_darat_project.appendChild(me._ht_node_custom_image7);
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
		el=me._txthotspot5=document.createElement('div');
		els=me._txthotspot5__text=document.createElement('div');
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
		me._txthotspot5.ggUpdateText=function() {
			var params = [];
			params.push(String(player._(me.hotspot.title)));
			var hs = player._("%1", params);
			if (hs!=this.ggText) {
				this.ggText=hs;
				this.ggTextDiv.innerHTML=hs;
				if (this.ggUpdatePosition) this.ggUpdatePosition();
			}
		}
		me._txthotspot5.ggUpdateText();
		player.addListener('changenode', function() {
			me._txthotspot5.ggUpdateText();
		});
		el.appendChild(els);
		me._txthotspot5.ggIsActive=function() {
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
		me._txthotspot5.ggUpdatePosition=function (useTransition) {
		}
		me._hotspot_darat_project.appendChild(me._txthotspot5);
		me.elementMouseOver['hotspot_darat_project']=false;
		if ((hotspot) && (hotspot.customimage)) {
			me._ht_node_custom_image7.style.width=hotspot.customimagewidth + 'px';
			me._ht_node_custom_image7.style.height=hotspot.customimageheight + 'px';
			let d = 0;
			me._ht_node_custom_image7.style.left='calc(50% - ' + ((hotspot.customimagewidth)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
			d = 0;
			me._ht_node_custom_image7.style.top='calc(50% - ' + ((hotspot.customimageheight)/2 + 0) +'px' + ((d<0) ? ' - ' : ' + ') + d + '%)';
		}
		me._ht_node_custom_image7.logicBlock_visible();
		me._rectangle_10.logicBlock_scaling();
		me.elementMouseOver['rectangle_10']=false;
		me._ht_node_thumb_12.logicBlock_scaling();
		me.elementMouseOver['ht_node_thumb_12']=false;
			me.ggEvent_activehotspotchanged=function() {
				me._ht_node_custom_image7.logicBlock_visible();
			};
			me.ggEvent_changenode=function() {
				me._ht_node_custom_image7.logicBlock_visible();
			};
			me.ggEvent_configloaded=function() {
				me._ht_node_custom_image7.logicBlock_visible();
			};
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
			if (hotspot.skinid=='hotspot-360-out') {
				hotspot.skinid = 'hotspot-360-out';
				hsinst = new SkinHotspotClass_hotspot360out(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		} else
			if (hotspot.skinid=='nama_lokasi') {
				hotspot.skinid = 'nama_lokasi';
				hsinst = new SkinHotspotClass_nama_lokasi(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		} else
			if (hotspot.skinid=='hotspot_label') {
				hotspot.skinid = 'hotspot_label';
				hsinst = new SkinHotspotClass_hotspot_label(me, hotspot);
			if (!hotspotTemplates.hasOwnProperty(hotspot.skinid)) {
				hotspotTemplates[hotspot.skinid] = [];
			}
			hotspotTemplates[hotspot.skinid].push(hsinst);
		} else
		{
				hotspot.skinid = '360_video_experience_project_point';
				hsinst = new SkinHotspotClass__360_video_experience_project_point(me, hotspot);
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
	style.appendChild(document.createTextNode('.ggskin { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 14px; line-height: normal; } .ggmarkdown p,.ggmarkdown h1,.ggmarkdown h2,.ggmarkdown h3,.ggmarkdown h4 { margin-top: 0px } .ggmarkdown { white-space:normal } @keyframes apiGlow { 0% { box-shadow: 0 0 5px rgba(255, 80, 0, 0.6), 0 0 10px rgba(255, 120, 0, 0.5), 0 0 15px rgba(255, 180, 0, 0.4); } 100% { box-shadow: 0 0 10px rgba(255, 120, 0, 0.9), 0 0 20px rgba(255, 160, 0, 0.7), 0 0 30px rgba(255, 220, 0, 0.6); } }'));
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
};