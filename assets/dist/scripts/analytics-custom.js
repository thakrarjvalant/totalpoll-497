jQuery(document).ready(function() {
  jQuery(document).on('click','#export-pdf-btn',function(){
    if(jQuery('.poll-dropdown').val() != ''){
      let doc = new jsPDF();
      doc.addHTML(jQuery('.totalpoll-analytics-charts'),function() {
        doc.save('poll-analytics.pdf');
      });
    }else{
      alert('Please select a poll from the dropdown');
    }
  })
});
