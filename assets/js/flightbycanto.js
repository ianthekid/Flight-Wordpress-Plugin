var FlightbyCanto = React.createClass({

    render: function() {

        var displayItem = (item) => <div style={divStyle}>{item}</div>;

        return (
            <div>
                { this.props.src.map(displayItem) }
            </div>
        );
    }

});

React.render(<FlightbyCanto source={path} />, document.getElementById('fbc-loop') );
