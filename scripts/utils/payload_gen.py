"""
Payload generator - builds minimal valid payloads from OpenAPI schemas.
"""
from typing import Dict, Any, List, Optional, Tuple
import random


class PayloadGenerator:
    """Generates minimal valid payloads from OpenAPI parameter and schema definitions."""

    def __init__(self, domain_seeds: Optional[Dict[str, Any]] = None, config: Optional[Dict[str, Any]] = None):
        """
        Initialize payload generator.

        Args:
            domain_seeds: Dictionary of discovered domain data (dealers, customers, etc.)
            config: Configuration dictionary from env_loader
        """
        self.domain_seeds = domain_seeds or {}
        self.config = config or {}

    def update_seeds(self, seeds: Dict[str, Any]):
        """Update domain seeds with new discovered data."""
        self.domain_seeds.update(seeds)

    def generate_from_schema(self, schema: Dict[str, Any], context: str = '') -> Any:
        """
        Generate a minimal valid value from a JSON schema.

        Args:
            schema: JSON schema definition
            context: Context string for debugging

        Returns:
            Generated value matching the schema
        """
        schema_type = schema.get('type')

        # Handle enum
        if 'enum' in schema and schema['enum']:
            return schema['enum'][0]

        # Handle type-specific generation
        if schema_type == 'string':
            return self._generate_string(schema, context)
        elif schema_type == 'integer':
            return self._generate_integer(schema, context)
        elif schema_type == 'number':
            return self._generate_number(schema, context)
        elif schema_type == 'boolean':
            return schema.get('default', False)
        elif schema_type == 'array':
            return self._generate_array(schema, context)
        elif schema_type == 'object':
            return self._generate_object(schema, context)
        elif schema_type is None and 'properties' in schema:
            # Object type implied by properties
            return self._generate_object(schema, context)

        # Default fallback
        return None

    def _generate_string(self, schema: Dict[str, Any], context: str) -> str:
        """Generate a string value."""
        # Check for default
        if 'default' in schema:
            return schema['default']

        # Check for example
        if 'example' in schema:
            return schema['example']

        # Check format
        fmt = schema.get('format', '')
        if fmt == 'date':
            return '2024-01-01'
        elif fmt == 'date-time':
            return '2024-01-01T00:00:00Z'
        elif fmt == 'email':
            return 'test@example.com'
        elif fmt == 'uuid':
            return '00000000-0000-0000-0000-000000000000'

        # Check context for known patterns
        context_lower = context.lower()

        # Try to use domain seeds
        if 'dealerid' in context_lower or 'dealer_id' in context_lower:
            dealers = self.domain_seeds.get('dealers', [])
            if dealers:
                return dealers[0].get('id', 'DEALER_ID')
            return 'DEALER_ID'

        if 'dealercode' in context_lower or 'dealer_code' in context_lower or context_lower == 'code':
            # Try config first
            if self.config.get('dealer_code'):
                return self.config['dealer_code']
            # Then domain seeds
            dealers = self.domain_seeds.get('dealers', [])
            if dealers:
                return dealers[0].get('code', 'DEALER_CODE')
            return 'DEALER_CODE'

        if 'customerid' in context_lower or 'customer_id' in context_lower:
            customers = self.domain_seeds.get('customers', [])
            if customers:
                return customers[0].get('id', 'CUSTOMER_ID')
            return 'CUSTOMER_ID'

        if 'locationid' in context_lower or 'location_id' in context_lower:
            locations = self.domain_seeds.get('locations', [])
            if locations:
                return locations[0].get('id', 'LOCATION_ID')
            return 'LOCATION_ID'

        # Pattern-based defaults
        if 'id' in context_lower:
            return 'ID_PLACEHOLDER'
        if 'code' in context_lower:
            return 'CODE_PLACEHOLDER'
        if 'name' in context_lower:
            return 'Test Name'
        if 'email' in context_lower:
            return 'test@example.com'

        # Fallback
        min_length = schema.get('minLength', 0)
        if min_length > 0:
            return 'x' * min_length
        return 'test'

    def _generate_integer(self, schema: Dict[str, Any], context: str) -> int:
        """Generate an integer value."""
        if 'default' in schema:
            return schema['default']
        if 'example' in schema:
            return schema['example']

        minimum = schema.get('minimum', 0)
        maximum = schema.get('maximum', 100)

        # Context-based defaults
        context_lower = context.lower()
        if 'page' in context_lower:
            return 1
        if 'limit' in context_lower or 'size' in context_lower:
            return 50

        return minimum

    def _generate_number(self, schema: Dict[str, Any], context: str) -> float:
        """Generate a number value."""
        if 'default' in schema:
            return schema['default']
        if 'example' in schema:
            return schema['example']

        minimum = schema.get('minimum', 0.0)
        return minimum

    def _generate_array(self, schema: Dict[str, Any], context: str) -> List[Any]:
        """Generate an array value."""
        # For minimal payload, return empty array unless minItems requires more
        min_items = schema.get('minItems', 0)
        if min_items == 0:
            return []

        # Generate minimal items
        items_schema = schema.get('items', {})
        result = []
        for i in range(min_items):
            result.append(self.generate_from_schema(items_schema, f'{context}[{i}]'))

        return result

    def _generate_object(self, schema: Dict[str, Any], context: str) -> Dict[str, Any]:
        """Generate an object value."""
        result = {}
        properties = schema.get('properties', {})
        required = schema.get('required', [])

        # Only include required properties for minimal payload
        for prop_name in required:
            if prop_name in properties:
                prop_schema = properties[prop_name]
                prop_context = f'{context}.{prop_name}' if context else prop_name
                result[prop_name] = self.generate_from_schema(prop_schema, prop_context)

        return result

    def generate_query_params(
        self,
        parameters: List[Dict[str, Any]],
    ) -> Dict[str, Any]:
        """
        Generate query parameters from OpenAPI parameter definitions.
        Supports both Swagger 2.0 and OpenAPI 3.0 formats.

        Args:
            parameters: List of parameter definitions

        Returns:
            Dictionary of query parameters
        """
        params = {}

        for param in parameters:
            if param.get('in') != 'query':
                continue

            name = param.get('name')
            if not name:
                continue

            # Only include required params for minimal payload
            required = param.get('required', False)
            if not required:
                continue

            # Generate value from schema
            # In OpenAPI 3.0, parameters have a 'schema' field
            # In Swagger 2.0, type/format are directly on the parameter
            schema = param.get('schema')
            if not schema:
                # Swagger 2.0 format - build schema from parameter fields
                schema = {
                    'type': param.get('type', 'string'),
                    'format': param.get('format'),
                    'enum': param.get('enum'),
                    'default': param.get('default'),
                    'example': param.get('example'),
                }

            params[name] = self.generate_from_schema(schema, name)

        return params

    def generate_path_params(
        self,
        path: str,
        parameters: List[Dict[str, Any]],
    ) -> Tuple[str, Dict[str, Any]]:
        """
        Generate path parameters and return filled path.
        Supports both Swagger 2.0 and OpenAPI 3.0 formats.

        Args:
            path: Path template with {param} placeholders
            parameters: List of parameter definitions

        Returns:
            Tuple of (filled_path, path_params_dict)
        """
        path_params = {}
        filled_path = path

        for param in parameters:
            if param.get('in') != 'path':
                continue

            name = param.get('name')
            if not name:
                continue

            # Generate value from schema
            # In OpenAPI 3.0, parameters have a 'schema' field
            # In Swagger 2.0, type/format are directly on the parameter
            schema = param.get('schema')
            if not schema:
                # Swagger 2.0 format - build schema from parameter fields
                schema = {
                    'type': param.get('type', 'string'),
                    'format': param.get('format'),
                    'enum': param.get('enum'),
                    'default': param.get('default'),
                    'example': param.get('example'),
                }

            value = self.generate_from_schema(schema, name)
            path_params[name] = value

            # Replace in path
            filled_path = filled_path.replace(f'{{{name}}}', str(value))

        return filled_path, path_params

    def generate_request_body(
        self,
        request_body_spec: Dict[str, Any],
    ) -> Optional[Dict[str, Any]]:
        """
        Generate request body from OpenAPI requestBody definition.

        Args:
            request_body_spec: requestBody specification

        Returns:
            Generated request body or None
        """
        if not request_body_spec:
            return None

        # Get content type (prefer application/json)
        content = request_body_spec.get('content', {})
        if 'application/json' not in content:
            return None

        json_content = content['application/json']
        schema = json_content.get('schema', {})

        if not schema:
            return None

        return self.generate_from_schema(schema, 'body')

    def enumerate_enum_variations(
        self,
        parameters: List[Dict[str, Any]],
    ) -> List[Dict[str, Any]]:
        """
        Generate variations of query params for each enum value.

        Args:
            parameters: List of parameter definitions

        Returns:
            List of parameter dictionaries (one per enum variation)
        """
        variations = [{}]

        for param in parameters:
            if param.get('in') != 'query':
                continue

            name = param.get('name')
            schema = param.get('schema', {})

            # Check if it has enum
            if 'enum' in schema:
                enum_values = schema['enum']
                # Create variations for each enum value
                new_variations = []
                for variation in variations:
                    for enum_val in enum_values:
                        new_var = variation.copy()
                        new_var[name] = enum_val
                        new_variations.append(new_var)
                variations = new_variations
            else:
                # Add default value to all variations
                value = self.generate_from_schema(schema, name)
                for variation in variations:
                    if param.get('required', False):
                        variation[name] = value

        return variations if variations != [{}] else []
