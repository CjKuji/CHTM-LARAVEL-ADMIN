function roomAvailabilityCalendar({ availability, rooms, roomTypes }) {
    const now = new Date();
    const monthLabels = Array.from({ length: 12 }, (_, i) =>
        new Date(2000, i, 1).toLocaleString('default', { month: 'long' })
    );

    return {
        onDayClick(day) {
            const el = this.$el || document.querySelector('[data-reservation-url]');
            const reservationUrl = el?.dataset?.reservationUrl || null;
            const tab = el?.dataset?.tab || this.tab || 'pending';
            const booking = this.findBookingIdForDay(day);
            if (booking && reservationUrl) {
                try {
                    const url = new URL(reservationUrl, window.location.origin);
                    url.searchParams.set('booking', booking);
                    url.searchParams.set('tab', tab);
                    window.location.href = url.toString();
                } catch (e) {
                    // fallback
                    window.location.href = reservationUrl + '?booking=' + booking + '&tab=' + tab;
                }
            }
        },

        findBookingIdForDay(day) {
            const targetDate = this.normalize(new Date(this.selectedYear, this.selectedMonth, day));
            const roomIds = this.roomIdsForType;
            for (const b of this.availability) {
                if (!roomIds.has(b.room_id)) continue;
                const start = this.parseDate(b.start_at);
                const end = this.parseDate(b.end_at);
                if (!start || !end) continue;
                if (!['approved', 'checked_in', 'in_progress'].includes(b.status)) continue;
                if (this.isInRange(targetDate, start, end)) return b.id || b.booking_id || null;
            }
            return null;
        },

        availability,
        rooms,
        roomTypes,
        monthLabels,
        selectedRoomTypeId: null,
        selectedMonth: now.getMonth(),
        selectedYear: now.getFullYear(),

        get daysInMonth() {
            const count = new Date(this.selectedYear, this.selectedMonth + 1, 0).getDate();
            return Array.from({ length: count }, (_, i) => i + 1);
        },

        get roomIdsForType() {
            if (!this.selectedRoomTypeId) {
                return new Set();
            }
            return new Set(
                this.rooms
                    .filter((r) => r.room_type?.id === this.selectedRoomTypeId)
                    .map((r) => r.id)
            );
        },

        normalize(d) {
            return new Date(d.getFullYear(), d.getMonth(), d.getDate());
        },

        parseDate(value) {
            if (!value) {
                return null;
            }
            const d = new Date(value);
            if (isNaN(d.getTime())) {
                return null;
            }
            return this.normalize(d);
        },

        isInRange(target, start, end) {
            return target.getTime() >= start.getTime() && target.getTime() <= end.getTime();
        },

        isBooked(day) {
            if (!this.selectedRoomTypeId) {
                return false;
            }

            const targetDate = this.normalize(
                new Date(this.selectedYear, this.selectedMonth, day)
            );
            const roomIds = this.roomIdsForType;

            return this.availability.some((b) => {
                if (!roomIds.has(b.room_id)) {
                    return false;
                }

                const start = this.parseDate(b.start_at);
                const end = this.parseDate(b.end_at);
                if (!start || !end) {
                    return false;
                }

                if (!['approved', 'checked_in', 'in_progress'].includes(b.status)) {
                    return false;
                }

                return this.isInRange(targetDate, start, end);
            });
        },
    };
}


