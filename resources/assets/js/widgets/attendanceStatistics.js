/** 
 * 
 * Sales
 * 
**/

window.addEventListener("load", function(){
    try {
  
      let getcorkThemeObject = sessionStorage.getItem("theme");
      let getParseObject = JSON.parse(getcorkThemeObject)
      let ParsedObject = getParseObject;
  
      if (ParsedObject.settings.layout.darkMode) {
        
        var Theme = 'dark';
    
        Apex.tooltip = {
            theme: Theme
        }
    
        /**
            ==============================
            |    @Options Charts Script   |
            ==============================
        */
        
        /*
            ==================================
                Sales By Category | Options
            ==================================
        */
        var options = {
            chart: {
                type: 'pie',
                width: 270,
                height: 430
            },
            colors: ASdata['AScolors'],
            dataLabels: {
              enabled: false
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '14px',
                markers: {
                  width: 10,
                  height: 10,
                  offsetX: -5,
                  offsetY: 0
                },
                itemMargin: {
                  horizontal: 10,
                  vertical: 10
                }
            },
            stroke: {
              // show: true,
              // width: 15,
              colors: ['#0e1726']
            },
            series: ASdata['ASchartvalue'],
            labels: ASdata['ASlabel'],
        }
  
      } else {
  
        var Theme = 'dark';
    
        Apex.tooltip = {
            theme: Theme
        }
    
        /**
            ==============================
            |    @Options Charts Script   |
            ==============================
        */
        
        /*
            ==================================
                Sales By Category | Options
            ==================================
        */
        var options = {
            chart: {
                type: 'pie',
                width: 270,
                height: 430
            },
            colors: ASdata['AScolors'],
            dataLabels: {
              enabled: false
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '14px',
                markers: {
                  width: 10,
                  height: 10,
                  offsetX: -5,
                  offsetY: 0
                },
                itemMargin: {
                  horizontal: 10,
                  vertical: 10
                }
            },
            stroke: {
              // show: true,
              // width: 15,
              colors: ['#fff']
            },
            series: ASdata['ASchartvalue'],
            labels: ASdata['ASlabel'],
        }
      }
      
    
    /**
        ==============================
        |    @Render Charts Script    |
        ==============================
    */
    
    /*
        =================================
            Sales By Category | Render
        =================================
    */
    var chart = new ApexCharts(
        document.querySelector("#attendance-statistics"),
        options
    );
    
    chart.render();
  
    /**
       * =================================================================================================
       * |     @Re_Render | Re render all the necessary JS when clicked to switch/toggle theme           |
       * =================================================================================================
       */
    
    document.querySelector('.theme-toggle').addEventListener('click', function() {
  
      // console.log(sessionStorage);
  
      let getcorkThemeObject = sessionStorage.getItem("theme");
      let getParseObject = JSON.parse(getcorkThemeObject)
      let ParsedObject = getParseObject;
  
      if (ParsedObject.settings.layout.darkMode) {  
  
        /*
        ==================================
            Sales By Category | Options
        ==================================
        */
  
        chart.updateOptions({
          stroke: {
            colors: ['#0e1726']
          },
          plotOptions: {
            pie: {
              donut: {
                labels: {
                  value: {
                    color: '#bfc9d4'
                  }
                }
              }
            }
          }
        })
  
      } else {
  
  
        /*
        ==================================
            Sales By Category | Options
        ==================================
        */
  
        chart.updateOptions({
          stroke: {
            colors: ['#fff']
          },
          plotOptions: {
            pie: {
              donut: {
                labels: {
                  value: {
                    color: '#0e1726'
                  }
                }
              }
            }
          }
        })        
        
      }
  
    })
    
    
    } catch(e) {
        console.log(e);
    }
})