"""
Error analyzer - analyzes API errors and suggests payload adjustments.
"""
import re
from typing import Dict, Any, List, Optional, Tuple


class ErrorAnalyzer:
    """Analyzes API error responses and suggests adjustments."""

    def analyze_error(
        self,
        status_code: int,
        response_data: Dict[str, Any],
        response_text: str,
        query_params: Dict[str, Any],
        request_body: Optional[Dict[str, Any]],
    ) -> List[Dict[str, Any]]:
        """
        Analyze an error response and suggest payload adjustments.

        Args:
            status_code: HTTP status code
            response_data: Parsed response data
            response_text: Raw response text
            query_params: Query parameters used
            request_body: Request body used

        Returns:
            List of suggested adjustments, each with 'type' and 'changes' keys
        """
        suggestions = []

        # Extract error message
        error_msg = self._extract_error_message(response_data, response_text)

        # Analyze by status code
        if status_code == 400:
            suggestions.extend(self._analyze_bad_request(error_msg, query_params, request_body))
        elif status_code == 401:
            suggestions.extend(self._analyze_unauthorized(error_msg))
        elif status_code == 403:
            suggestions.extend(self._analyze_forbidden(error_msg, query_params))
        elif status_code == 404:
            suggestions.extend(self._analyze_not_found(error_msg, query_params))
        elif status_code == 422:
            suggestions.extend(self._analyze_unprocessable(error_msg, query_params, request_body))

        # Analyze error message patterns
        suggestions.extend(self._analyze_message_patterns(error_msg, query_params, request_body))

        return suggestions

    def _extract_error_message(self, response_data: Dict[str, Any], response_text: str) -> str:
        """Extract error message from response."""
        # Try common error message fields
        msg = (
            response_data.get('Message') or
            response_data.get('message') or
            response_data.get('error') or
            response_data.get('Error') or
            response_data.get('error_description') or
            ''
        )

        # Check for errors array
        if 'Errors' in response_data and isinstance(response_data['Errors'], list):
            errors = response_data['Errors']
            if errors:
                first_error = errors[0]
                if isinstance(first_error, dict):
                    msg = first_error.get('Description') or first_error.get('Code') or msg

        # Fallback to raw text
        if not msg:
            msg = response_text[:500]

        return str(msg).lower()

    def _analyze_bad_request(
        self,
        error_msg: str,
        query_params: Dict[str, Any],
        request_body: Optional[Dict[str, Any]]
    ) -> List[Dict[str, Any]]:
        """Analyze 400 Bad Request errors."""
        suggestions = []

        # Check for missing required parameters
        if 'required' in error_msg or 'missing' in error_msg:
            # Try to extract parameter name
            match = re.search(r"(?:parameter|field|property)[\s:'\"]*(\w+)", error_msg)
            if match:
                param_name = match.group(1)
                suggestions.append({
                    'type': 'add_parameter',
                    'parameter': param_name,
                    'reason': f'Missing required parameter: {param_name}'
                })

        # Check for invalid format
        if 'invalid' in error_msg or 'format' in error_msg:
            for param, value in query_params.items():
                if param in error_msg:
                    suggestions.append({
                        'type': 'change_format',
                        'parameter': param,
                        'current_value': value,
                        'reason': f'Invalid format for {param}'
                    })

        return suggestions

    def _analyze_unauthorized(self, error_msg: str) -> List[Dict[str, Any]]:
        """Analyze 401 Unauthorized errors."""
        suggestions = []

        if 'token' in error_msg or 'expired' in error_msg:
            suggestions.append({
                'type': 'refresh_token',
                'reason': 'Token may be expired or invalid'
            })

        return suggestions

    def _analyze_forbidden(
        self,
        error_msg: str,
        query_params: Dict[str, Any]
    ) -> List[Dict[str, Any]]:
        """Analyze 403 Forbidden errors."""
        suggestions = []

        # Access denied typically means wrong ID/code
        if 'access denied' in error_msg or 'permission' in error_msg:
            # Check which parameter might be wrong
            for param in ['code', 'id', 'customercode', 'dealerid', 'customerid']:
                if param in query_params:
                    suggestions.append({
                        'type': 'try_alternative',
                        'parameter': param,
                        'reason': f'Access denied - try different {param}'
                    })

        return suggestions

    def _analyze_not_found(
        self,
        error_msg: str,
        query_params: Dict[str, Any]
    ) -> List[Dict[str, Any]]:
        """Analyze 404 Not Found errors."""
        suggestions = []

        # Entity not found - try different ID
        for param in ['id', 'code', 'customerid', 'dealerid']:
            if param in query_params:
                suggestions.append({
                    'type': 'try_alternative',
                    'parameter': param,
                    'reason': f'Not found - try different {param}'
                })

        return suggestions

    def _analyze_unprocessable(
        self,
        error_msg: str,
        query_params: Dict[str, Any],
        request_body: Optional[Dict[str, Any]]
    ) -> List[Dict[str, Any]]:
        """Analyze 422 Unprocessable Entity errors."""
        suggestions = []

        # Validation errors - extract field names
        fields = re.findall(r"field[\s:'\"]*(\w+)", error_msg)
        for field in fields:
            suggestions.append({
                'type': 'fix_validation',
                'field': field,
                'reason': f'Validation error on field: {field}'
            })

        return suggestions

    def _analyze_message_patterns(
        self,
        error_msg: str,
        query_params: Dict[str, Any],
        request_body: Optional[Dict[str, Any]]
    ) -> List[Dict[str, Any]]:
        """Analyze error message for common patterns."""
        suggestions = []

        # Check for null/empty values
        if 'null' in error_msg or 'empty' in error_msg:
            for param, value in query_params.items():
                if value is None or value == '' or str(value).lower() == 'null':
                    suggestions.append({
                        'type': 'provide_value',
                        'parameter': param,
                        'current_value': value,
                        'reason': f'Parameter {param} is null/empty'
                    })

        # Check for type mismatches
        if 'type' in error_msg or 'expected' in error_msg:
            match = re.search(r"expected (\w+)", error_msg)
            if match:
                expected_type = match.group(1)
                suggestions.append({
                    'type': 'change_type',
                    'expected_type': expected_type,
                    'reason': f'Type mismatch - expected {expected_type}'
                })

        return suggestions


def apply_suggestions(
    suggestions: List[Dict[str, Any]],
    query_params: Dict[str, Any],
    request_body: Optional[Dict[str, Any]],
    config: Dict[str, Any],
    domain_seeds: Dict[str, Any],
    attempt: int
) -> Tuple[Dict[str, Any], Optional[Dict[str, Any]]]:
    """
    Apply suggestions to generate new payload.

    Args:
        suggestions: List of suggestions from error analyzer
        query_params: Current query parameters
        request_body: Current request body
        config: Configuration dict
        domain_seeds: Domain seeds dict
        attempt: Current attempt number

    Returns:
        Tuple of (new_query_params, new_request_body)
    """
    new_query_params = query_params.copy()
    new_request_body = request_body.copy() if request_body else None

    for suggestion in suggestions:
        stype = suggestion.get('type')

        if stype == 'provide_value':
            param = suggestion.get('parameter')
            if param:
                # Try to provide a better value
                new_val = _get_better_value(param, config, domain_seeds, attempt)
                if new_val:
                    new_query_params[param] = new_val

        elif stype == 'try_alternative':
            param = suggestion.get('parameter')
            if param:
                # Try next alternative from domain seeds
                new_val = _get_alternative_value(param, config, domain_seeds, attempt)
                if new_val:
                    new_query_params[param] = new_val

        elif stype == 'add_parameter':
            param = suggestion.get('parameter')
            if param and param not in new_query_params:
                # Add missing parameter
                new_val = _get_better_value(param, config, domain_seeds, attempt)
                if new_val:
                    new_query_params[param] = new_val

    return new_query_params, new_request_body


def _get_better_value(param: str, config: Dict[str, Any], domain_seeds: Dict[str, Any], attempt: int) -> Any:
    """Get a better value for a parameter."""
    param_lower = param.lower()

    # Try config first
    if param_lower in ['code', 'dealercode', 'dealer_code']:
        return config.get('dealer_code')
    if param_lower in ['dealerid', 'dealer_id']:
        return config.get('dealer_id')

    # Try domain seeds
    if param_lower in ['customercode', 'customer_code']:
        customers = domain_seeds.get('customers', [])
        if customers and attempt < len(customers):
            return customers[attempt].get('code')

    if param_lower in ['customerid', 'customer_id']:
        customers = domain_seeds.get('customers', [])
        if customers and attempt < len(customers):
            return customers[attempt].get('id')

    return None


def _get_alternative_value(param: str, config: Dict[str, Any], domain_seeds: Dict[str, Any], attempt: int) -> Any:
    """Get an alternative value for a parameter (cycle through options)."""
    return _get_better_value(param, config, domain_seeds, attempt)
