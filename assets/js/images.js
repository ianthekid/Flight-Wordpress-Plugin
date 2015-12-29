var Posts = React.createClass({
	
	onItemClick: function(e) {
		console.log(e);		
	},
	
    render: function() {

        var displayPosts = function(data) {
			
			//console.log(data[0]);
			
            return (
                <li tabindex="0" role="checkbox" data-id={data[0].id} data-name={data[0].name} className="fbc_attachment attachment save-ready details">
                    <div className="attachment-preview js--select-attachment type-image subtype-jpeg landscape">
                        <div className="thumbnail" onClick={this.onItemClick}>
                            <div className="centered">
                                <img src={data[0].img} draggable="false" alt={data[0].name} />
                            </div>
                        </div>
                    </div>
                    <a className="check" href="#" title="Deselect" tabindex="0">
                        <div className="media-modal-icon"></div>
                    </a>
                </li>
            );
        }

        return (
            <ul tabindex="-1" className="attachments" id="__attachments-view-fbc">
                { this.props.data.map(displayPosts) }
            </ul>
        );
    }

});

var PostApp = React.createClass({
    getInitialState: function() {
        return {
			data: []
        };
    },

    repeat: function(item) {
        var self = this;
        $.ajax({
            url: args.FBC_URL +"/includes/lib/download.php?id="+ item.id +"&subdomain="+ args.subdomain +"&token="+ args.token,
            //url: "download.php?id="+ item.id,
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

                //console.log(e);

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
                <Posts data={this.state.data} />
            </div>
        );
    }

});

var path = args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&token="+ args.token +"&limit=30&start=0";
React.render(<PostApp source={path} />, document.getElementById('fbc-loop') );
