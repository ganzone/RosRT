
from dahua_wrapper import DahuaRpc

dahua = DahuaRpc(host="192.168.10.90", username="admin", password='RosignanoM2022#')
dahua.login()


# Get the ANPR Plate Numbers by using the following
# Get the object id
object_id = dahua.get_traffic_info() 

# Use the object id to find the Plate Numbers
dahua.start_find(object_id=object_id) 

# Find and dump the Plate Numbers
#response = json.dumps(dahua.do_find(object_id=object_id))

