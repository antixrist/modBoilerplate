modBoilerplate.panel.Home = function (config) {
	config = config || {};
	Ext.apply(config, {
		baseCls: 'modx-formpanel',
		layout: 'anchor',
		/*
		 stateful: true,
		 stateId: 'modboilerplate-panel-home',
		 stateEvents: ['tabchange'],
		 getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
		 */
		hideMode: 'offsets',
		items: [{
			html: '<h2>' + _('modboilerplate') + '</h2>',
			cls: '',
			style: {margin: '15px 0'}
		}, {
			xtype: 'modx-tabs',
			defaults: {border: false, autoHeight: true},
			border: true,
			hideMode: 'offsets',
			items: [{
				title: _('modboilerplate_items'),
				layout: 'anchor',
				items: [{
					html: _('modboilerplate_intro_msg'),
					cls: 'panel-desc',
				}, {
					xtype: 'modboilerplate-grid-items',
					cls: 'main-wrapper',
				}]
			}]
		}]
	});
	modBoilerplate.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(modBoilerplate.panel.Home, MODx.Panel);
Ext.reg('modboilerplate-panel-home', modBoilerplate.panel.Home);
