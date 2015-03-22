modBoilerplate.page.Home = function (config) {
	config = config || {};
	Ext.applyIf(config, {
		components: [{
			xtype: 'modboilerplate-panel-home', renderTo: 'modboilerplate-panel-home-div'
		}]
	});
	modBoilerplate.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(modBoilerplate.page.Home, MODx.Component);
Ext.reg('modboilerplate-page-home', modBoilerplate.page.Home);