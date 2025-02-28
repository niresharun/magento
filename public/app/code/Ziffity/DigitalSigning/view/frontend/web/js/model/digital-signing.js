define([], function() {
    return {
        image:null,
        agreement:false,
        setImage: function(imageData) {
            this.image = imageData;
            return this;
        },
        getImage: function() {
            return this.image;
        },
        setAgreement: function(agreement) {
            this.agreement = agreement;
            return this.agreement;
        },
        getAgreement: function() {
            return this.agreement;
        }
    };
})
