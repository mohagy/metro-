enum PaperSize {
  a4,
  letter,
  legal,
  a3,
  a5,
}

enum PrintColor {
  blackWhite,
  color,
}

enum PrintSides {
  single,
  double,
}

enum Orientation {
  portrait,
  landscape,
}

enum BindingType {
  none,
  staple,
  spiral,
  perfectBinding,
}

class PrintOption {
  final PaperSize paperSize;
  final PrintColor color;
  final int quantity;
  final PrintSides sides;
  final Orientation orientation;
  final BindingType binding;

  PrintOption({
    this.paperSize = PaperSize.a4,
    this.color = PrintColor.blackWhite,
    this.quantity = 1,
    this.sides = PrintSides.single,
    this.orientation = Orientation.portrait,
    this.binding = BindingType.none,
  });

  Map<String, dynamic> toMap() {
    return {
      'paperSize': paperSize.name,
      'color': color.name,
      'quantity': quantity,
      'sides': sides.name,
      'orientation': orientation.name,
      'binding': binding.name,
    };
  }

  factory PrintOption.fromMap(Map<String, dynamic> map) {
    return PrintOption(
      paperSize: PaperSize.values.firstWhere(
        (e) => e.name == map['paperSize'],
        orElse: () => PaperSize.a4,
      ),
      color: PrintColor.values.firstWhere(
        (e) => e.name == map['color'],
        orElse: () => PrintColor.blackWhite,
      ),
      quantity: map['quantity'] ?? 1,
      sides: PrintSides.values.firstWhere(
        (e) => e.name == map['sides'],
        orElse: () => PrintSides.single,
      ),
      orientation: Orientation.values.firstWhere(
        (e) => e.name == map['orientation'],
        orElse: () => Orientation.portrait,
      ),
      binding: BindingType.values.firstWhere(
        (e) => e.name == map['binding'],
        orElse: () => BindingType.none,
      ),
    );
  }

  PrintOption copyWith({
    PaperSize? paperSize,
    PrintColor? color,
    int? quantity,
    PrintSides? sides,
    Orientation? orientation,
    BindingType? binding,
  }) {
    return PrintOption(
      paperSize: paperSize ?? this.paperSize,
      color: color ?? this.color,
      quantity: quantity ?? this.quantity,
      sides: sides ?? this.sides,
      orientation: orientation ?? this.orientation,
      binding: binding ?? this.binding,
    );
  }

  String get paperSizeLabel {
    switch (paperSize) {
      case PaperSize.a4:
        return 'A4';
      case PaperSize.letter:
        return 'Letter';
      case PaperSize.legal:
        return 'Legal';
      case PaperSize.a3:
        return 'A3';
      case PaperSize.a5:
        return 'A5';
    }
  }

  String get colorLabel {
    return color == PrintColor.color ? 'Color' : 'Black & White';
  }

  String get sidesLabel {
    return sides == PrintSides.single ? 'Single-sided' : 'Double-sided';
  }

  String get orientationLabel {
    return orientation == Orientation.portrait ? 'Portrait' : 'Landscape';
  }

  String get bindingLabel {
    switch (binding) {
      case BindingType.none:
        return 'None';
      case BindingType.staple:
        return 'Staple';
      case BindingType.spiral:
        return 'Spiral';
      case BindingType.perfectBinding:
        return 'Perfect Binding';
    }
  }
}

