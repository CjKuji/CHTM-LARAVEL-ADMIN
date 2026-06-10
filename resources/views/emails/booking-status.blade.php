<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Update</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 20px; background-color: #f4f6f8;">

    <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 650px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        
        <tr>
            @if($status === 'approved')
                <td style="background-color: #d53f8c; padding: 25px; text-align: center;">
                    <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">Reservation Confirmed</h1>
                </td>
            @else
                <td style="background-color: #c53030; padding: 25px; text-align: center;">
                    <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">Booking Update</h1>
                </td>
            @endif
        </tr>

        <tr>
            <td style="padding: 30px;">
                <h2 style="margin-top: 0; color: #2d3748; font-size: 20px;">
    Hello, {{ $booking->user ? $booking->user->fullName() : 'Valued Guest' }}!
</h2>

                @if($status === 'approved')
                    <p style="color: #4a5568; font-size: 16px; margin-bottom: 25px;">
                        We are happy to let you know that your booking request has been <strong>approved</strong>! Here are your complete trip details:
                    </p>

                    <h3 style="color: #d53f8c; border-bottom: 2px solid #fbb6ce; padding-bottom: 6px; margin-bottom: 15px; font-size: 14px; text-transform: uppercase;">
                        Room Information
                    </h3>
                    <table width="100%" style="margin-bottom: 25px; font-size: 14px; color: #4a5568;" cellpadding="5">
                        <tr>
                            <td width="35%" style="font-weight: bold; color: #718096;">Your Room:</td>
                            <td><span style="background-color: #e6fffa; color: #319795; padding: 4px 8px; border-radius: 4px; font-weight: bold;">Room {{ $booking->room->room_number ?? 'N/A' }}</span></td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #718096;">Room Type:</td>
                            <td>{{ $booking->room->roomType->name ?? $booking->room->room_type->name ?? 'Standard Suite' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #718096;">Floor Location:</td>
                            <td>Floor {{ $booking->room->floor ?? '1' }}</td>
                        </tr>
                    </table>

                    <h3 style="color: #d53f8c; border-bottom: 2px solid #fbb6ce; padding-bottom: 6px; margin-bottom: 15px; font-size: 14px; text-transform: uppercase;">
                        Stay Dates
                    </h3>
                    <table width="100%" style="margin-bottom: 25px; font-size: 14px; color: #4a5568;" cellpadding="5">
                        <tr>
                            <td width="35%" style="font-weight: bold; color: #718096;">Check-In Time:</td>
                            <td>{{ $booking->start_at ? $booking->start_at->format('F d, Y \a\t h:i A') : '—' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #718096;">Check-Out Time:</td>
                            <td>{{ $booking->end_at ? $booking->end_at->format('F d, Y \a\t h:i A') : '—' }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #718096;">Total Length:</td>
                            <td>
                                @if($booking->start_at && $booking->end_at)
                                    {{ $booking->start_at->diffInDays($booking->end_at) }} Night(s)
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    </table>

                    <h3 style="color: #d53f8c; border-bottom: 2px solid #fbb6ce; padding-bottom: 6px; margin-bottom: 15px; font-size: 14px; text-transform: uppercase;">
                        Guests & Special Accommodations
                    </h3>
                    <table width="100%" style="margin-bottom: 25px; font-size: 14px; color: #4a5568;" cellpadding="5">
                        <tr>
                            <td width="35%" style="font-weight: bold; color: #718096;">Number of Guests:</td>
                            <td>{{ $booking->guests ?? 1 }} person(s) (Extra Beds: {{ $booking->extra_beds ?? 0 }})</td>
                        </tr>
                        <tr>
                            <td style="font-weight: bold; color: #718096;">Age Profiles & Care:</td>
                            <td>
                                @if($booking->has_pwd || $booking->has_senior || $booking->has_child)
                                    <div style="margin-top: 2px;">
                                        @if($booking->has_pwd) <span style="background: #feebc8; color: #c05621; padding: 2px 6px; border-radius: 4px; margin-right: 5px; font-size: 12px; font-weight: bold;">PWD Access</span> @endif
                                        @if($booking->has_senior) <span style="background: #edf2f7; color: #4a5568; padding: 2px 6px; border-radius: 4px; margin-right: 5px; font-size: 12px; font-weight: bold;">Senior Citizen</span> @endif
                                        @if($booking->has_child) <span style="background: #e2fbf5; color: #0d6855; padding: 2px 6px; border-radius: 4px; font-size: 12px; font-weight: bold;">Child ({{ $booking->child_age_group ?? 'General' }})</span> @endif
                                    </div>
                                @else
                                    <span style="color: #a0aec0;">Standard Layout (No Special Requirements flagged)</span>
                                @endif
                            </td>
                        </tr>
                        @if($booking->message)
                        <tr>
                            <td style="font-weight: bold; color: #718096; vertical-align: top; padding-top: 8px;">Special Requests:</td>
                            <td style="font-style: italic; color: #2d3748; background: #f7fafc; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                "{{ $booking->message }}"
                            </td>
                        </tr>
                        @endif
                    </table>

                    <h3 style="color: #d53f8c; border-bottom: 2px solid #fbb6ce; padding-bottom: 6px; margin-bottom: 15px; font-size: 14px; text-transform: uppercase;">
                        Price & Payment Matrix
                    </h3>
                    <div style="background: #f7fafc; padding: 15px; border-left: 4px solid #d53f8c; border-radius: 0 4px 4px 0; margin-bottom: 20px;">
                        <table width="100%" style="font-size: 15px;">
                            <tr>
                                <td style="color: #4a5568; font-weight: bold;">Rate at Time of Booking:</td>
                                <td align="right" style="color: #4a5568;">₱{{ number_format((float)$booking->price_at_booking, 2) }}</td>
                            </tr>
                            <tr>
                                <td style="color: #38a169; font-weight: bold; padding-top: 8px;">Total Settlement Amount:</td>
                                <td align="right" style="color: #38a169; font-weight: bold; font-size: 18px; padding-top: 8px;">
                                    ₱{{ number_format((float)$booking->total_amount, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; color: #718096; padding-top: 8px;">Authorized Method:</td>
                                <td align="right" style="font-size: 12px; color: #4a5568; padding-top: 8px; text-transform: capitalize; font-weight: bold;">
                                    {{ str_replace('_', ' ', $booking->payment_method ?? 'Pay on Arrival / Undefined') }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="text-align: center; margin: 30px 0 15px 0;">
                        <span style="background-color: #38a169; color: #ffffff; padding: 10px 20px; border-radius: 20px; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Approved Status Active</span>
                    </div>

                    <p style="margin-top: 25px; color: #4a5568; text-align: center;">Please carefully check over these details. We look forward to having you stay with us!</p>

                @else
                    <p style="color: #c53030; font-size: 16px; font-weight: bold; margin-bottom: 10px;">
                        We are very sorry, but the room is not available for your selected dates.
                    </p>
                    <p style="color: #4a5568; margin-bottom: 25px;">
                        Because of a scheduling conflict, we could not accept your booking request for the dates between 
                        <strong>{{ $booking->start_at ? $booking->start_at->format('M d, Y') : '—' }}</strong> and 
                        <strong>{{ $booking->end_at ? $booking->end_at->format('M d, Y') : '—' }}</strong>.
                    </p>

                    <div style="background: #fffaf0; border: 1px solid #feebc8; border-radius: 6px; padding: 20px; margin-bottom: 20px;">
                        <h4 style="margin: 0 0 15px 0; color: #dd6b20; font-size: 15px; text-transform: uppercase;">
                            Other Places to Stay Nearby (Olongapo & Subic Bay)
                        </h4>
                        <p style="font-size: 13px; color: #718096; margin-top: -10px; margin-bottom: 15px;">
                            To help you with your travel plans, we highly recommend checking out these nearby alternative hotels:
                        </p>

                        <div style="background: #ffffff; padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 10px;">
                            <table width="100%">
                                <tr>
                                    <td>
                                        <strong style="color: #2d3748; font-size: 14px;">Central Park Reef Resort</strong><br>
                                        <span style="font-size: 12px; color: #718096;">Barretto, Olongapo City (Beachfront)</span>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <span style="background: #c6f6d5; color: #22543d; font-size: 12px; padding: 3px 8px; border-radius: 12px; font-weight: bold;">★ 8.5 / 10</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="background: #ffffff; padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 10px;">
                            <table width="100%">
                                <tr>
                                    <td>
                                        <strong style="color: #2d3748; font-size: 14px;">The Lighthouse Marina Resort</strong><br>
                                        <span style="font-size: 12px; color: #718096;">Moonbay Marina, Subic Bay Freeport Zone</span>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <span style="background: #c6f6d5; color: #22543d; font-size: 12px; padding: 3px 8px; border-radius: 12px; font-weight: bold;">★ 8.6 / 10</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="background: #ffffff; padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 10px;">
                            <table width="100%">
                                <tr>
                                    <td>
                                        <strong style="color: #2d3748; font-size: 14px;">Ibis Styles Subic</strong><br>
                                        <span style="font-size: 12px; color: #718096;">Rizal Highway (Right next to SM City Olongapo)</span>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <span style="background: #c6f6d5; color: #22543d; font-size: 12px; padding: 3px 8px; border-radius: 12px; font-weight: bold;">★ 8.4 / 10</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="background: #ffffff; padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px;">
                            <table width="100%">
                                <tr>
                                    <td>
                                        <strong style="color: #2d3748; font-size: 14px;">Best Western Plus Hotel Subic</strong><br>
                                        <span style="font-size: 12px; color: #718096;">Dewey Avenue, Central Business District</span>
                                    </td>
                                    <td align="right" style="vertical-align: middle;">
                                        <span style="background: #c6f6d5; color: #22543d; font-size: 12px; padding: 3px 8px; border-radius: 12px; font-weight: bold;">★ 8.3 / 10</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <p style="font-size: 14px; color: #4a5568;">If you would like to pick different dates or have queries regarding refunds, you can reply directly to this email message.</p>
                @endif

                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;">
                <p style="font-size: 12px; color: #718096; text-align: center; margin: 0;">
                    This is an automated notification about your reservation status tracking profile.
                </p>
            </td>
        </tr>
    </table>

</body>
</html>