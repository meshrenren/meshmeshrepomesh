 
    import _forEach from 'lodash/forEach'
    import cloneDeep from 'lodash/cloneDeep'
    
export const getNameList = {
    data() {
        return {
            allNameList     : [],
            memberList      : [],
            stationList     : [],
            divisionList    : []        
        }
    },
    methods: {
        mergeAll(member = null, division = null, station = null){
            let list = []
            if(member && member.length > 0){
                _forEach(member, rs =>{
                    let stationName = ""
                    let getStation = this.stationList.find(st => { return Number(st.id) == Number(rs.station_id)})
                    if(getStation){
                        stationName = getStation.name
                    }

                    let arr = {
                        value           : 'member-' + rs.id,
                        label           : rs.first_name + " " + rs.middle_name + " " + rs.last_name,
                        station_name    : stationName
                    }

                    list.push(arr)
                })
            }


            if(station && station.length > 0){
                _forEach(station, rs =>{
                    let arr = {
                        value   : 'station-' + rs.id,
                        label   : rs.name
                    }

                    list.push(arr)
                })
            }

            if(division && division.length > 0){
                _forEach(division, rs =>{
                    let arr = {
                        value   : 'division-' + rs.id,
                        label   : rs.name
                    }

                    list.push(arr)
                })
            }

            this.allNameList = list
        },
    }
}
