"""
JSON Schema validator for response validation.
"""
from typing import Dict, Any, List, Tuple, Optional


class SchemaValidator:
    """Simple JSON Schema validator for response validation."""

    def validate(
        self,
        data: Any,
        schema: Dict[str, Any],
        path: str = '$'
    ) -> Tuple[bool, List[str]]:
        """
        Validate data against a JSON schema.

        Args:
            data: Data to validate
            schema: JSON schema
            path: Current path in data (for error messages)

        Returns:
            Tuple of (is_valid, list_of_errors)
        """
        errors = []

        # Check type
        schema_type = schema.get('type')
        if schema_type:
            if not self._check_type(data, schema_type):
                errors.append(f"{path}: expected type '{schema_type}', got '{type(data).__name__}'")
                return False, errors

        # Validate based on type
        if schema_type == 'object' or 'properties' in schema:
            obj_errors = self._validate_object(data, schema, path)
            errors.extend(obj_errors)

        elif schema_type == 'array':
            array_errors = self._validate_array(data, schema, path)
            errors.extend(array_errors)

        elif schema_type == 'string':
            string_errors = self._validate_string(data, schema, path)
            errors.extend(string_errors)

        elif schema_type == 'integer' or schema_type == 'number':
            number_errors = self._validate_number(data, schema, path)
            errors.extend(number_errors)

        # Check enum
        if 'enum' in schema:
            if data not in schema['enum']:
                errors.append(f"{path}: value '{data}' not in enum {schema['enum']}")

        return len(errors) == 0, errors

    def _check_type(self, data: Any, schema_type: str) -> bool:
        """Check if data matches the schema type."""
        type_map = {
            'string': str,
            'integer': int,
            'number': (int, float),
            'boolean': bool,
            'array': list,
            'object': dict,
            'null': type(None),
        }

        expected_type = type_map.get(schema_type)
        if expected_type is None:
            return True  # Unknown type, skip check

        return isinstance(data, expected_type)

    def _validate_object(
        self,
        data: Any,
        schema: Dict[str, Any],
        path: str
    ) -> List[str]:
        """Validate object type."""
        errors = []

        if not isinstance(data, dict):
            errors.append(f"{path}: expected object, got {type(data).__name__}")
            return errors

        properties = schema.get('properties', {})
        required = schema.get('required', [])

        # Check required properties
        for prop in required:
            if prop not in data:
                errors.append(f"{path}: missing required property '{prop}'")

        # Validate properties
        for prop_name, prop_value in data.items():
            if prop_name in properties:
                prop_schema = properties[prop_name]
                prop_path = f"{path}.{prop_name}"
                is_valid, prop_errors = self.validate(prop_value, prop_schema, prop_path)
                errors.extend(prop_errors)

        return errors

    def _validate_array(
        self,
        data: Any,
        schema: Dict[str, Any],
        path: str
    ) -> List[str]:
        """Validate array type."""
        errors = []

        if not isinstance(data, list):
            errors.append(f"{path}: expected array, got {type(data).__name__}")
            return errors

        # Check min/max items
        min_items = schema.get('minItems')
        if min_items is not None and len(data) < min_items:
            errors.append(f"{path}: array has {len(data)} items, minimum is {min_items}")

        max_items = schema.get('maxItems')
        if max_items is not None and len(data) > max_items:
            errors.append(f"{path}: array has {len(data)} items, maximum is {max_items}")

        # Validate items
        items_schema = schema.get('items')
        if items_schema:
            for i, item in enumerate(data):
                item_path = f"{path}[{i}]"
                is_valid, item_errors = self.validate(item, items_schema, item_path)
                errors.extend(item_errors)

        return errors

    def _validate_string(
        self,
        data: Any,
        schema: Dict[str, Any],
        path: str
    ) -> List[str]:
        """Validate string type."""
        errors = []

        if not isinstance(data, str):
            errors.append(f"{path}: expected string, got {type(data).__name__}")
            return errors

        # Check min/max length
        min_length = schema.get('minLength')
        if min_length is not None and len(data) < min_length:
            errors.append(f"{path}: string length {len(data)} is less than minimum {min_length}")

        max_length = schema.get('maxLength')
        if max_length is not None and len(data) > max_length:
            errors.append(f"{path}: string length {len(data)} exceeds maximum {max_length}")

        # Check pattern
        pattern = schema.get('pattern')
        if pattern:
            import re
            if not re.match(pattern, data):
                errors.append(f"{path}: string does not match pattern '{pattern}'")

        return errors

    def _validate_number(
        self,
        data: Any,
        schema: Dict[str, Any],
        path: str
    ) -> List[str]:
        """Validate number/integer type."""
        errors = []

        if not isinstance(data, (int, float)):
            errors.append(f"{path}: expected number, got {type(data).__name__}")
            return errors

        # Check minimum
        minimum = schema.get('minimum')
        if minimum is not None and data < minimum:
            errors.append(f"{path}: value {data} is less than minimum {minimum}")

        # Check maximum
        maximum = schema.get('maximum')
        if maximum is not None and data > maximum:
            errors.append(f"{path}: value {data} exceeds maximum {maximum}")

        return errors


def resolve_schema_ref(ref: str, swagger: Dict[str, Any]) -> Optional[Dict[str, Any]]:
    """
    Resolve a $ref reference in OpenAPI spec.

    Args:
        ref: Reference string like "#/components/schemas/Device"
        swagger: Full swagger/OpenAPI document

    Returns:
        Resolved schema or None if not found
    """
    if not ref.startswith('#/'):
        return None

    parts = ref[2:].split('/')  # Remove '#/' and split
    current = swagger

    for part in parts:
        if isinstance(current, dict) and part in current:
            current = current[part]
        else:
            return None

    return current if isinstance(current, dict) else None
