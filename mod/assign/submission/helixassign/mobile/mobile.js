 
var that = this;
var result = {
    componentInit: function() {
        console.log("moodlemobile medial plugin init");

        const url = new URL(window.location);
        var parts = url.pathname.split('/');
        this.submission.url = '/mod/assign/view.php?id='+parts[5]+'&action=editsubmission';
        return true;
    }

};
   
result;
