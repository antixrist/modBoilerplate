var modBoilerplate = function (config) {
	config = config || {};
	modBoilerplate.superclass.constructor.call(this, config);
};
Ext.extend(modBoilerplate, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('modboilerplate', modBoilerplate);

modBoilerplate = new modBoilerplate();