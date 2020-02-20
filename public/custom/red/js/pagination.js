var newscripts=new virtualpaginate({
                    piececlass: "virtualpage2",
                    piececontainer: 'li', //Let script know you're using "p" tags as separator (instead of default "div")
                    pieces_per_page: 1,
                    defaultpage: 0,
                    wraparound: false,
                    persist: true
                })
                
                newscripts.buildpagination(["scriptspaginate"])