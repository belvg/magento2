Mage.Menu_Catalog = function(){
    var menu;
    return {
        init : function(){
            menu = new Ext.menu.Menu({
                id: 'mainCatalogMenu',
                items: [
                    new Ext.menu.Item({
                        text: 'Categories and Products',
                        handler: Mage.Catalog.loadMainPanel.createDelegate(Mage.Catalog)
                    }),
                    '-',
/*
                    new Ext.menu.Item({
                        text: 'Category attributes',
                        handler: Mage.Catalog_Category_Attributes.loadAttributesPanel.createDelegate(Mage.Catalog_Category_Attributes)                        
                    }),
*/
                    new Ext.menu.Item({
                        text: 'Product attributes',  
                        handler: Mage.Catalog_Product_Attributes.loadMainPanel.createDelegate(Mage.Catalog_Product_Attributes)                                                
                    })
/*
                    '-',
                    new Ext.menu.Item({
                        text: 'Product datafeeds'                  
                    })
*/
                 ]
            });
            Mage.Core.addLeftToolbarItem({
                cls: 'x-btn-text bmenu',
                text:'Catalog',
                menu: menu
            });
        }
    }
}();
Mage.Menu_Catalog.init();
