define([], function() {
    return {
        purchase:null,
        orderNotes:null,
        setPurchaseOrder: function(value) {
            this.purchase = value;
            return this;
        },
        getPurchaseOrder: function() {
            return this.purchase;
        },
        setOrderNotes: function(orderNotes) {
            this.orderNotes = orderNotes;
            return this;
        },
        getOrderNotes: function() {
            return this.orderNotes;
        }
    };
})