function roomAvailabilityCalendar({ availability, rooms, roomTypes }) {
    const now = new Date();
    const monthLabels = Array.from({ length: 12 }, (_, i) =>
        new Date(2000, i, 1).toLocaleString('default', { month: 'long' })
    );

    return {
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
