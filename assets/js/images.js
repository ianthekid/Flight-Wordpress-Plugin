var Images = React.createClass({
	getInitialState: function() {
		return {
			start: parseInt(args.start),
			limit: parseInt(args.limit),
			item: []
		};
	},

	handleClick: function(item,e) {
		this.setState({
			item: [item]
		});
	},

	componentDidUpdate: function(prevProps,prevState) {
		React.render(<Attachment attachment={this.state.item} />, document.getElementById('fbc_media-sidebar') );
	},

    render: function() {

		if( this.state.start > 0 )
			var addMore = this.state.start;

        return (
			<span>
				{ this.props.data.map(function(item, i) {

					var divStyle = {
						backgroundImage: 'url(' + item[0].img + ')',
					};

					return (
						<li className="fbc_attachment attachment">
			                <div className="attachment-preview" style={divStyle} onClick={this.handleClick.bind(this,item[0])}>
								<a href={item[0].img} className="fullscreen" data-featherlight="image">
									<i className="icon-resize"></i>
								</a>
			                </div>
			            </li>
					);
				}, this)}
			</span>
        );
    }

});

var FlightImages = React.createClass({
	getInitialState: function() {
		return {
			src: this.props.path,
			album: {name: 'Recent Images'},
			search: this.props.search,
			//start: parseInt(args.start),
			//limit: parseInt(args.limit),
			start: 0,
			limit: 30,
			data: []
		};
	},

	loadMore: function(e) {
		jQuery('#loader').show();
		this.setState({
			start: this.state.start+this.state.limit,
			src: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&token="+ args.token +"&album="+ this.state.album.id +"&limit="+ this.state.limit +"&start="+ (this.state.start+this.state.limit)
		});
	},

    repeat: function(item,cnt,length,found) {
        var self = this;
        $.ajax({
            url: args.FBC_URL +"/includes/lib/download.php?id="+ item.id +"&subdomain="+ args.subdomain +"&token="+ args.token +"&limit="+ this.state.limit +"&start="+ this.state.start
		})
		.done(function(e) {
			var start = e.search("Location: ");
			var stop = e.search("Server: ");
			var imgFile = e.substring( (start+10) ,stop);

			var expires = imgFile.split('?Expire');
			var img = expires[0];

			var fileExt = img.split('.').pop();
			var ext = fileExt.split('%');
			ext[0] = ext[0].toLowerCase();

			if(ext[0] == "jpg" || ext[0] == "jpeg" || ext[0] == "gif" || ext[0] == "png" || ext[0] == "pdf") {
                var image = [{
                    "id": item.id,
                    "name": item.name,
                    "owner": item.owner,
                    "ownerName": item.ownerName,
                    "size": item.size,
                    "time": item.time,
                    "img": imgFile
                }];

				var arr = self.state.data.slice();
                arr.push(image);
                self.setState({data: arr});
			}

			if(cnt == length) {
				jQuery('#loader').hide();

				if(found > (self.state.start+self.state.limit))
					jQuery('#fbc_loadMore').show();
				else
					jQuery('#fbc_loadMore').hide();
			}

        })
		.always(function() {
		});
    },

	componentDidMount: function() {
		jQuery('#loader').show();
		var self = this;
		$.ajax({
			url: this.state.src,
			dataType: 'json',
			cache: false
		})
		.done(function(data) {
			var cnt = 1;
            $.each(data.results, function(k,v) {
                self.repeat(v,cnt,data.results.length,data.found);
				cnt++;
            });
		});
	},

	componentWillUpdate: function(nextProps,nextState) {
		if(nextProps.album.id != this.state.album.id) {
			this.setState({
				album: nextProps.album,
				start: 0,
				data: [],
				src: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&album="+ nextProps.album.id +"&token="+ args.token +"&limit="+ this.state.limit +"&start=0"
			});
		}
		if(nextProps.search != this.state.search) {
			this.setState({
				album: {
					name: 'Search Results: '+nextProps.search
				},
				search: nextProps.search,
				start: 0,
				data: [],
				src: args.FBC_URL +"/includes/lib/get.php?subdomain="+ args.subdomain +"&keyword="+ nextProps.search +"&token="+ args.token +"&limit="+ this.state.limit +"&start=0"
			});
		}
	},

	looper: function() {
		var self = this;
		$.ajax({
			url: this.state.src,
			dataType: 'json',
			cache: false
		})
		.done(function(data) {
			var cnt = 1;
			$.each(data.results, function(k,v) {
                self.repeat(v,cnt,data.results.length,data.found);
				cnt++;
			});
		});
	},

	componentDidUpdate: function(prevProps,prevState) {
		if(this.state.album.id != prevState.album.id) {
			jQuery('#fbc_loadMore').hide();
			jQuery('#loader').show();
			this.looper();
		}
		if(this.state.search != prevState.search) {
			jQuery('#fbc_loadMore').hide();
			jQuery('#loader').show();
			this.looper();
		}
		if(this.state.start > prevState.start) {
			this.looper();
		}
	},

    render: function() {
        return (
			<div class="grid">
				<h1 className="text-center">{this.state.album.name}</h1>
				<ul className="attachments" id="__attachments-view-fbc">
	                <Images data={this.state.data} />

					<div id="fbc_loadMore_wrap">
						<button className="btn" id="fbc_loadMore" onClick={this.loadMore}>Load More</button>
					</div>
	            </ul>
			</div>
        );
    }
});
