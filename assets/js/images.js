var Images = React.createClass({

	getInitialState: function() {
		return {item: null};
	},

	handleClick: function(item,e) {
		this.setState({item: item});
	},

    render: function() {
        return (
            <ul className="attachments" id="__attachments-view-fbc">
				{ this.props.data.map(function(item, i) {
					return (
						<li className="fbc_attachment attachment">
			                <div className="attachment-preview">
	                            <img src={item[0].img} onClick={this.handleClick.bind(this,item[0])} />
			                </div>
			            </li>
					);
				}, this)}
            </ul>
        );
    }

});

var FlightImages = React.createClass({
    getInitialState: function() {
        return {
			data: []
        };
    },

    repeat: function(item) {
        var self = this;
        $.ajax({
            url: args.FBC_URL +"/includes/lib/download.php?id="+ item.id +"&subdomain="+ args.subdomain +"&token="+ args.token,
            success: function(e) {
                var start = e.search("Location: ");
                var stop = e.search("Server: ");
                var img = e.substring( (start+10) ,stop);

                var arr = self.state.data.slice();
                var image = [{
                    "id": item.id,
                    "name": item.name,
                    "owner": item.owner,
                    "ownerName": item.ownerName,
                    "size": item.size,
                    "time": item.time,
                    "img": img
                }];

                arr.push(image);
                self.setState({data: arr});
            }
        });
    },

	componentDidMount: function() {
		$.ajax({
			url: this.props.source,
			dataType: 'json',
			cache: false,
			success: function(data) {
                var self = this;
                $.each(data, function(k,v) {
                    self.repeat(v);
                });
			}.bind(this),
			error: function(xhr, status, err) {
				console.error(this.props.source, status, err.toString());
			}.bind(this)
		});
	},

    render: function() {
        return (
            <div class="grid">
                <Images data={this.state.data} />
            </div>
        );
    }
});

var path = args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&token="+ args.token +"&limit=30&start=0";
React.render(<FlightImages source={path} />, document.getElementById('fbc-loop') );
