<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice</title>
</head>
<body style="font-family: Arial, sans-serif;">

    @if (!empty($products))
        @foreach ($products as $index => $product)
            <div>

                <!-- Header: Store Details -->
                <table style="width: 100%; margin-bottom: 1rem;">
                    <tr>
                        <!-- QR Code & Store Name -->
                        <td>
                            <table>
                                <tr>
                                    <!-- QR Code -->
                                    @if (!empty($store['qr_code_file_path']))
                                        @php
                                            $imageData = file_get_contents($store['qr_code_file_path']);
                                            $imageType = pathinfo($store['qr_code_file_path'], PATHINFO_EXTENSION);
                                            $base64Image = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);
                                        @endphp
                                        <td style="padding-right: 12px;">
                                            <img src="{{ $base64Image }}" alt="Store QR Code" style="width: 64px; height: 64px; border-radius: 8px;">
                                        </td>
                                    @endif

                                    <!-- Logo -->
                                    @if (!empty($store['logo']))
                                        @php
                                            $imageData = file_get_contents($store['logo']['path']);
                                            $imageType = pathinfo($store['logo']['path'], PATHINFO_EXTENSION);
                                            $base64Image = 'data:image/' . $imageType . ';base64,' . base64_encode($imageData);
                                        @endphp
                                        <td style="padding-right: 12px;">
                                            <img src="{{ $base64Image }}" alt="Store Logo" style="width: 64px; height: 64px; border-radius: 8px;">
                                        </td>
                                    @endif

                                    <!-- Store Name & Link -->
                                    <td style="vertical-align: middle;">
                                        <p style="font-size: 20px; font-weight: bold; margin: 0;">{{ $store['name'] ?? 'Store' }}</p>
                                        <p style="font-size: 14px; color: #4b5563; margin: 0;">{{ $store['web_link'] ?? '#' }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- Product Title -->
                        <td style="width: 30%; text-align: right; font-size: 32px; font-weight: bold; text-transform: uppercase; vertical-align: middle;">
                            Product
                        </td>
                    </tr>
                </table>

                <p>{{ $product['name'] }}</p>

                <p style="margin-top: 24px; text-align: center; color: #6b7280;">Thank you!</p>

            </div>

            <!-- Page Break for PDF -->
            @if ($index < count($products) - 1)
                <div style="page-break-after: always;"></div>
            @endif
        @endforeach
    @endif

</body>
</html>
