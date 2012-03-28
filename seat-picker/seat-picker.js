// Place picker enables users to pick their place in a room
// Depends on jQuery and jQuery UI Draggable

var LanOrg = LanOrg || {};

(function ($) {

  // @param parent Element which the place picker is appended to
  // @param width Width, in pixel, of the room map
  // @param height Height, in pixel, of the room map
  LanOrg.Room = function (parent, room_width, room_height,
                          seat_width, seat_height) {

    var wrapper_el = $('<DIV/>').appendTo(parent).addClass('lanorg-seat-picker');

    var add_button = $('<INPUT TYPE="BUTTON" VALUE="Add"/>').appendTo(wrapper_el);
    add_button.click('click', $.proxy(this.createNewSeat, this));

    var remove_button = $('<INPUT TYPE="BUTTON" VALUE="Delete"/>').appendTo(wrapper_el);
    remove_button.click('click', $.proxy(this.deleteSelectedSeat, this));

    var room_el = $('<DIV/>').appendTo(wrapper_el);

    room_el.css('position', 'relative').resizable({animate: true});

    room_el.addClass('lanorg-room');

    // Place picker main element
    this.room_element_ = room_el;

    room_el.width(room_width + 'px').height(room_height + 'px');

    // Room dimensions, in pixel
    this.room_width_ = room_width;
    this.room_height_ = room_height;

    // Seat dimensions, in pixel
    this.seat_width_ = seat_width;
    this.seat_height_ = seat_height;

    // Seat currently selected
    this.selected_seat_ = null;

    // X/Y position for new elements
    this.next_x_ = 10;
    this.next_y_ = 10;

    // Current z-index, which is increased when a seat is selected
    this.z_index_ = 1;

    // List of seat objects
    this.seats_ = [];
  };

  // Adds a new place in room
  LanOrg.Room.prototype.addSeat = function (seat) {
    // Set seat width and height
    seat.element_.width(this.seat_width_ + 'px').height(this.seat_height_ + 'px');


    seat.element_.appendTo(this.room_element_);

    seat.attachTo_(this);

    seat.element_.bind('mousedown', seat, $.proxy(this.handleSeatSelection, this));

    this.seats_.push(seat);
  };

  // Delete the supplied seat from the room list
  LanOrg.Room.prototype.deleteSeat = function (seat) {
    if (seat != null) {
      var removed = false;
      var self = this;
      $.each(this.seats_, function (index, seat_iter) {
        if (seat.compare(seat_iter)) {
          self.seats_.splice(index, 1);
          seat_iter.element_.remove();
          removed = true;
        }
        return !removed;
      });
      if (removed && this.seats_.length > 0) {
        this.selectSeat(this.seats_[0]);
      }
    }
  };

  LanOrg.Room.prototype.handleSeatSelection = function (e) {
    var seat = e.data;

    this.selectSeat(seat);
  };

  LanOrg.Room.prototype.selectSeat = function (seat) {
    $.each(this.seats_, function (index, seat) {
      seat.element_.removeClass('lanorg-seat-selected');
    });

    if (seat) {
      seat.element_.css('z-index', this.z_index_);
      this.z_index_++;

      seat.element_.addClass('lanorg-seat-selected');
    }
    this.selected_seat_ = seat;
  };

  // Creates a new empty seat
  LanOrg.Room.prototype.createNewSeat = function () {
    var seat = new LanOrg.RoomSeat(this.next_x_, this.next_y_, 1);

    this.next_x_ += 10;
    this.next_y_ += 10;
    if (this.next_x_ + this.seat_width_ >= this.room_width_) {
      this.next_x_ = 10;
    }
    if (this.next_y_ + this.seat_height_ >= this.room_height_) {
      this.next_y_ = 10;
    }

    this.addSeat(seat);
  };

  LanOrg.Room.prototype.deleteSelectedSeat = function () {
    this.deleteSeat(this.selected_seat_);
  };

  // Seat class
  LanOrg.RoomSeat = function (x, y, state) {
    this.x_ = x;
    this.y_ = y;

    this.state_ = state;

    var el = $('<DIV/>');

    // Seat element
    this.element_ = el;

    // unique id used to compare seats together
    this.unique_id_ = ++LanOrg.RoomSeat.UniqueID;

    el.addClass('lanorg-seat');

    el.css('position', 'absolute');
    this.setPosition(x, y);
  };

  // Unique id used to compare seats together
  LanOrg.RoomSeat.UniqueID = 0;

  LanOrg.RoomSeat.prototype.compare = function (other) {
    return this.unique_id_ == other.unique_id_;
  };

  // Sets the seat X/Y position
  LanOrg.RoomSeat.prototype.setPosition = function (x, y) {
    this.element_.css('left', x + 'px').css('top', y);
    this.x_ = x;
    this.y_ = y;
  };

  LanOrg.RoomSeat.prototype.attachTo_ = function (room) {
    this.element_.draggable({
      containment: room.room_element_, scroll: false
    });
    this.element_.bind('drag', this, $.proxy(this.handleDragMove, this));
  };

  LanOrg.RoomSeat.prototype.handleDragMove = function (e, ui) {
    this.x_ = ui.position.left;
    this.y_ = ui.position.top;
  };

})(jQuery);
